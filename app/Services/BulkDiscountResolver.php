<?php

namespace App\Services;

use App\Models\Admin\Additional;
use App\Models\Admin\BulkDiscount;
use App\Models\Admin\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for bulk discount pricing.
 *
 * Rules:
 *  - Discount is always calculated off regular_price, never off current_price,
 *    so repeated runs can never compound.
 *  - Product level campaigns (priority 2) beat category level campaigns (priority 1).
 *  - When the winning campaign ends the product falls back to the next winning
 *    campaign, and only falls back to regular_price when nothing covers it.
 *  - Prices are written to BOTH products and product_additionals, because
 *    variants hold their own copy of the price columns.
 */
class BulkDiscountResolver
{
    /**
     * Expand a campaign to the product ids it covers.
     *
     * Category campaigns are expanded live, so a product added to a category
     * mid-campaign is picked up on the next sync.
     */
    public function productIdsFor(BulkDiscount $campaign): array
    {
        if ($campaign->target_type === BulkDiscount::TARGET_PRODUCT) {
            return DB::table('bulk_discount_products')
                ->where('bulk_discount_id', $campaign->id)
                ->pluck('product_id')
                ->all();
        }

        $categoryIds = DB::table('bulk_discount_categories')
            ->where('bulk_discount_id', $campaign->id)
            ->pluck('category_id')
            ->all();

        if (empty($categoryIds)) {
            return [];
        }

        return $this->productIdsInCategories($categoryIds);
    }

    /**
     * Expand categories to themselves plus every descendant.
     *
     * Products hang off leaf categories in this catalogue, so a root like
     * "Boys Emotions" has no direct products at all. Selecting a parent has to
     * mean the whole branch or it would discount nothing.
     */
    public function expandCategoryIds(array $categoryIds): array
    {
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));

        if (empty($categoryIds)) {
            return [];
        }

        // One read of the tree, then walk it in memory. The table is small and
        // this avoids a query per level.
        $children = [];
        foreach (DB::table('categories')->select('id', 'parent_id')->get() as $category) {
            $parentId = (int) $category->parent_id;
            $children[$parentId][] = (int) $category->id;
        }

        $expanded = [];
        $queue = $categoryIds;

        while ($queue) {
            $id = array_shift($queue);

            // Guards against a cycle in parent_id, which would otherwise hang.
            if (isset($expanded[$id])) {
                continue;
            }

            $expanded[$id] = true;

            foreach ($children[$id] ?? [] as $childId) {
                $queue[] = $childId;
            }
        }

        return array_keys($expanded);
    }

    /**
     * Products belonging to any of the given categories or their descendants.
     *
     * Category membership lives in two places in this codebase: the
     * category_product pivot and the products.category_ids JSON column.
     * Both are checked so neither write path is missed.
     */
    public function productIdsInCategories(array $categoryIds): array
    {
        $categoryIds = $this->expandCategoryIds($categoryIds);

        if (empty($categoryIds)) {
            return [];
        }

        $fromPivot = DB::table('category_product')
            ->whereIn('category_id', $categoryIds)
            ->pluck('product_id')
            ->all();

        $fromJson = Product::query()
            ->where(function ($query) use ($categoryIds) {
                foreach ($categoryIds as $categoryId) {
                    $query->orWhereJsonContains('category_ids', $categoryId);
                    // category_ids is sometimes stored as strings
                    $query->orWhereJsonContains('category_ids', (string) $categoryId);
                }
            })
            ->pluck('id')
            ->all();

        return array_values(array_unique(array_merge($fromPivot, $fromJson)));
    }

    /**
     * All running campaigns, best first (product beats category, newest breaks ties).
     */
    public function runningCampaigns($now = null): Collection
    {
        return BulkDiscount::query()
            ->running($now)
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Map of product_id => winning campaign, for every product currently covered.
     */
    public function winnersByProduct($now = null): array
    {
        $winners = [];

        foreach ($this->runningCampaigns($now) as $campaign) {
            foreach ($this->productIdsFor($campaign) as $productId) {
                // Campaigns arrive best first, so the first writer wins.
                if (!isset($winners[$productId])) {
                    $winners[$productId] = $campaign;
                }
            }
        }

        return $winners;
    }

    /**
     * Discounted price for a regular price under a campaign.
     * Returns the regular price untouched when there is no campaign.
     */
    public function priceFor(?BulkDiscount $campaign, $regularPrice): float
    {
        $regularPrice = (float) $regularPrice;

        if (!$campaign) {
            return round($regularPrice, 2);
        }

        $discount = (float) $campaign->discount;

        // Matches the existing convention: 1 = Fixed, 0 = Parcentage
        if ((int) $campaign->discount_type === 1) {
            $price = $regularPrice - $discount;
        } else {
            $price = $regularPrice - (($regularPrice * $discount) / 100);
        }

        return round(max(0, $price), 2);
    }

    /**
     * Discount amount stored alongside the price, in the campaign's own unit.
     */
    protected function discountColumnsFor(?BulkDiscount $campaign): array
    {
        if (!$campaign) {
            return ['discount' => 0, 'discount_type' => 0];
        }

        return [
            'discount' => (float) $campaign->discount,
            'discount_type' => $campaign->discount_type,
        ];
    }

    /**
     * Recompute and persist prices for the given products.
     *
     * Pass null to recompute every product a campaign currently covers plus
     * every product a campaign still holds in the ledger, so expired campaigns
     * are rolled back.
     *
     * Products a campaign has never touched are never written. A discount typed
     * into the product edit form lives in the same columns as a campaign
     * discount, so the ledger, not the price, decides what this may modify.
     */
    public function sync(?array $productIds = null, $now = null): int
    {
        $winners = $this->winnersByProduct($now);

        // Products a campaign currently owns, according to the ledger.
        $applications = DB::table('bulk_discount_applications')
            ->get()
            ->keyBy('product_id');

        if ($productIds === null) {
            $productIds = array_values(array_unique(array_merge(
                array_keys($winners),
                $applications->pluck('product_id')->all()
            )));
        }

        $productIds = array_values(array_filter(array_map('intval', $productIds)));

        if (empty($productIds)) {
            return 0;
        }

        $touched = 0;

        foreach (array_chunk($productIds, 500) as $chunk) {
            $products = Product::whereIn('id', $chunk)->get(['id', 'regular_price', 'current_price', 'discount', 'discount_type']);

            DB::transaction(function () use ($products, $winners, $applications, &$touched) {
                foreach ($products as $product) {
                    $campaign = $winners[$product->id] ?? null;
                    $application = $applications->get($product->id);

                    // Nothing owns it and nothing owned it: not ours to touch.
                    if (!$campaign && !$application) {
                        continue;
                    }

                    // The price moved since the campaign wrote it, so an admin
                    // edited this product by hand. Their value wins; release it.
                    if ($application && round((float) $application->applied_price, 2) !== round((float) $product->current_price, 2)) {
                        DB::table('bulk_discount_applications')->where('product_id', $product->id)->delete();
                        continue;
                    }

                    // No campaign covers it any more: hand back exactly what the
                    // product had before the campaign, manual discount included.
                    if (!$campaign) {
                        $restore = [
                            'regular_price' => $application->original_regular_price,
                            'current_price' => $application->original_current_price,
                            'discount' => $application->original_discount,
                            'discount_type' => $application->original_discount_type,
                        ];

                        Product::where('id', $product->id)->update($restore);
                        Additional::where('product_id', $product->id)->update($restore);

                        DB::table('bulk_discount_applications')->where('product_id', $product->id)->delete();

                        $touched++;
                        continue;
                    }

                    // regular_price is the untouched base. Guard against products
                    // that never had one set, so a discount can't zero them out.
                    $regularPrice = (float) $product->regular_price;
                    if ($regularPrice <= 0) {
                        continue;
                    }

                    $newPrice = $this->priceFor($campaign, $regularPrice);
                    $discountColumns = $this->discountColumnsFor($campaign);

                    $unchanged = $application
                        && (int) $application->bulk_discount_id === (int) $campaign->id
                        && round((float) $product->current_price, 2) === $newPrice;

                    if ($unchanged) {
                        continue;
                    }

                    $payload = array_merge(['current_price' => $newPrice], $discountColumns);

                    Product::where('id', $product->id)->update($payload);

                    // Variants keep their own copy of the price columns.
                    Additional::where('product_id', $product->id)->update($payload);

                    // Record what we wrote, and what was there first. The
                    // original is preserved from the existing row so a second
                    // campaign taking over cannot overwrite the true original.
                    DB::table('bulk_discount_applications')->updateOrInsert(
                        ['product_id' => $product->id],
                        [
                            'bulk_discount_id' => $campaign->id,
                            'applied_price' => $newPrice,
                            'original_regular_price' => $application->original_regular_price ?? $product->regular_price,
                            'original_current_price' => $application->original_current_price ?? $product->current_price,
                            'original_discount' => $application->original_discount ?? $product->discount,
                            'original_discount_type' => $application->original_discount_type ?? $product->discount_type,
                            'updated_at' => now(),
                            'created_at' => $application->created_at ?? now(),
                        ]
                    );

                    $touched++;
                }
            });
        }

        return $touched;
    }

    /**
     * Recompute the products a campaign covers. Used after create, edit and end-now.
     */
    public function syncCampaign(BulkDiscount $campaign, array $alsoInclude = []): int
    {
        $productIds = array_merge($this->productIdsFor($campaign), $alsoInclude);

        return $this->sync($productIds);
    }

    /**
     * What a draft campaign would do, without saving anything.
     *
     * Returns one row per affected product including the campaign it would
     * override, so the admin sees conflicts before committing.
     */
    public function preview(array $draft, array $productIds, $limit = 500): array
    {
        $campaign = new BulkDiscount([
            'target_type' => $draft['target_type'] ?? BulkDiscount::TARGET_PRODUCT,
            'priority' => $draft['priority'] ?? BulkDiscount::PRIORITY_PRODUCT,
            'discount_type' => $draft['discount_type'] ?? 0,
            'discount' => $draft['discount'] ?? 0,
        ]);

        $ignoreId = $draft['ignore_id'] ?? null;

        // Who currently owns these products, excluding the campaign being edited.
        $currentWinners = [];
        foreach ($this->runningCampaigns() as $running) {
            if ($ignoreId && (int) $running->id === (int) $ignoreId) {
                continue;
            }

            foreach ($this->productIdsFor($running) as $productId) {
                if (!isset($currentWinners[$productId])) {
                    $currentWinners[$productId] = $running;
                }
            }
        }

        $products = Product::whereIn('id', $productIds)
            ->take($limit)
            ->get(['id', 'name', 'sku', 'regular_price', 'current_price']);

        // Where a campaign already rewrote the price, "Now" should show the
        // price the shopper sees, but the untouched regular price is still the
        // base every calculation runs from.
        $applications = DB::table('bulk_discount_applications')
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        $rows = [];
        $conflicts = [];

        foreach ($products as $product) {
            $existing = $currentWinners[$product->id] ?? null;
            $application = $applications->get($product->id);

            // A lower or equal priority campaign does not beat the incumbent.
            $wins = !$existing || $campaign->priority >= $existing->priority;

            $newPrice = $wins
                ? $this->priceFor($campaign, $product->regular_price)
                : $this->priceFor($existing, $product->regular_price);

            if ($existing) {
                $conflicts[$existing->id] = [
                    'id' => $existing->id,
                    'name' => $existing->name,
                    'wins' => $wins,
                ];
            }

            $rows[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'regular_price' => (float) $product->regular_price,
                'current_price' => (float) $product->current_price,
                // What the product reverts to if every campaign ends, so the
                // admin can see a manual discount that is being covered up.
                'original_price' => $application
                    ? (float) $application->original_current_price
                    : (float) $product->current_price,
                'new_price' => $newPrice,
                'applied' => $wins,
                'overrides' => $existing ? $existing->name : null,
            ];
        }

        return [
            'rows' => $rows,
            'total' => count($productIds),
            'shown' => count($rows),
            'conflicts' => array_values($conflicts),
        ];
    }
}
