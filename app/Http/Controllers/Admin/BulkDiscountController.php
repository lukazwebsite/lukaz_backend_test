<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Brand;
use App\Models\Admin\BulkDiscount;
use App\Models\Admin\Category;
use App\Models\Admin\Product;
use App\Services\BulkDiscountResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BulkDiscountController extends Controller
{
    protected $resolver;

    public function __construct(BulkDiscountResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Menu id is looked up by href instead of hardcoded, so the feature works
     * on any database whatever the seeded id ended up being.
     */
    private function menuId()
    {
        static $menuId = null;

        if ($menuId === null) {
            $menuId = DB::table('menus')->where('href', '/bulk_discount')->value('id');
        }

        return $menuId;
    }

    private function menuAccess()
    {
        return AccessUser::where('user_id', Auth::user()->id)
            ->where('menu_id', $this->menuId())
            ->get(['user_id', 'menu_id', 'action_id']);
    }

    private function denied()
    {
        return Inertia::render('auth/Unauthorize', [
            'message' => 'You do not have permission to access this page.',
        ]);
    }

    /**
     * Campaign list. This is the landing page, not a raw product grid.
     */
    public function index(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId(), 1);

        if (!$checkPermission) {
            return $this->denied();
        }

        $query = BulkDiscount::query()->withCount(['categoryLinks', 'productLinks']);

        $append = [];
        $now = now();

        if ($request->has('name') && $request->name !== null) {
            $query->where('name', 'like', '%' . $request->name . '%');
            $append['name'] = $request->name;
        }

        if ($request->has('target_type') && $request->target_type !== null) {
            $query->where('target_type', $request->target_type);
            $append['target_type'] = $request->target_type;
        }

        // Status is derived from the datetime window, never stored.
        if ($request->has('state') && $request->state !== null) {
            $state = $request->state;
            $append['state'] = $state;

            if ($state === 'active') {
                $query->where('status', 1)->where('starts_at', '<=', $now)->where('ends_at', '>=', $now);
            } elseif ($state === 'scheduled') {
                $query->where('status', 1)->where('starts_at', '>', $now);
            } elseif ($state === 'expired') {
                $query->where('status', 1)->where('ends_at', '<', $now);
            } elseif ($state === 'disabled') {
                $query->where('status', 0);
            }
        }

        $campaigns = $query->latest()->paginate(10)->appends($append)->withPath('/bulk_discount/paginate/filters');

        // Which campaign actually owns each product right now, so the list can
        // show where a product level campaign is beating a category one.
        $winners = $this->resolver->winnersByProduct($now);

        $coverage = [];
        foreach ($campaigns as $campaign) {
            $productIds = $this->resolver->productIdsFor($campaign);

            $overridden = 0;
            foreach ($productIds as $productId) {
                $winner = $winners[$productId] ?? null;
                if ($winner && (int) $winner->id !== (int) $campaign->id) {
                    $overridden++;
                }
            }

            $coverage[$campaign->id] = [
                'products' => count($productIds),
                'overridden' => $campaign->state === 'active' ? $overridden : 0,
            ];
        }

        return Inertia::render('bulk_discount/index', [
            'campaigns' => $campaigns,
            'coverage' => $coverage,
            'menuAccess' => $this->menuAccess(),
            'checkPermission' => $checkPermission,
            'append' => $append,
        ]);
    }

    /**
     * Create form. The product picker lives inside this page.
     */
    public function create(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId(), 2);

        if (!$checkPermission) {
            return $this->denied();
        }

        return Inertia::render('bulk_discount/create', [
            'categories' => $this->categoryTree(),
            'brands' => Brand::where('status', 1)->get(['id', 'name'])->sortBy('name')->values(),
        ]);
    }

    /**
     * Categories flattened into display order, each carrying its depth and its
     * descendants, so the picker can render the tree and select a whole branch.
     */
    private function categoryTree(): array
    {
        $categories = Category::where('status', 1)->get(['id', 'name', 'parent_id']);

        $byParent = [];
        foreach ($categories as $category) {
            $byParent[(int) $category->parent_id][] = $category;
        }

        $productCounts = DB::table('category_product')
            ->select('category_id', DB::raw('COUNT(DISTINCT product_id) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $flat = [];

        $walk = function ($parentId, $depth) use (&$walk, &$flat, $byParent, $productCounts) {
            $children = $byParent[$parentId] ?? [];

            usort($children, fn ($a, $b) => strcasecmp($a->name, $b->name));

            foreach ($children as $category) {
                $index = count($flat);

                $flat[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'parent_id' => $category->parent_id,
                    'depth' => $depth,
                    'own_products' => (int) ($productCounts[$category->id] ?? 0),
                    'descendant_ids' => [],
                ];

                $before = count($flat);
                $walk($category->id, $depth + 1);

                // Everything appended by the recursive call is a descendant.
                $descendants = [];
                for ($i = $before; $i < count($flat); $i++) {
                    $descendants[] = $flat[$i]['id'];
                }

                $flat[$index]['descendant_ids'] = $descendants;
            }
        };

        // Roots carry parent_id null, which casts to 0 in the grouping above.
        $walk(0, 0);

        // Anything whose parent is missing or inactive would otherwise be
        // unreachable from a root, so surface it rather than silently drop it.
        $emitted = array_column($flat, 'id');
        foreach ($categories as $category) {
            if (!in_array($category->id, $emitted, true)) {
                $flat[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'parent_id' => $category->parent_id,
                    'depth' => 0,
                    'own_products' => (int) ($productCounts[$category->id] ?? 0),
                    'descendant_ids' => [],
                ];
            }
        }

        return $flat;
    }

    /**
     * Paginated product list for the picker modal.
     *
     * Queried on Product, not Additional, so one row is one product rather
     * than one row per colour/size combination.
     */
    public function products(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId(), 1);

        if (!$checkPermission) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = (int) ($request->per_page ?: 20);
        if ($perPage < 1 || $perPage > 200) {
            $perPage = 20;
        }

        $query = Product::query()->with('brand:id,name');

        if ($request->filled('name')) {
            $query->where(function ($sub) use ($request) {
                $sub->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('sku', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('category_id')) {
            $productIds = $this->resolver->productIdsInCategories([$request->category_id]);
            $query->whereIn('id', $productIds ?: [0]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->orderBy('id', 'desc')
            ->paginate($perPage, ['id', 'name', 'sku', 'brand_id', 'regular_price', 'current_price', 'status'])
            ->appends($request->query());

        return response()->json($products);
    }

    /**
     * Resolve the product ids a draft targets, then price them, without saving.
     */
    public function preview(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId(), 1);

        if (!$checkPermission) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'target_type' => 'required|in:category,product',
            'discount_type' => 'required|in:0,1',
            'discount' => 'required|numeric|min:0',
        ]);

        $targetType = $request->target_type;

        if ($targetType === BulkDiscount::TARGET_CATEGORY) {
            $productIds = $this->resolver->productIdsInCategories($request->input('category_ids', []));
            $priority = BulkDiscount::PRIORITY_CATEGORY;
        } else {
            $productIds = array_map('intval', $request->input('product_ids', []));
            $priority = BulkDiscount::PRIORITY_PRODUCT;
        }

        $preview = $this->resolver->preview([
            'target_type' => $targetType,
            'priority' => $priority,
            'discount_type' => $request->discount_type,
            'discount' => $request->discount,
            'ignore_id' => $request->input('ignore_id'),
        ], $productIds);

        return response()->json($preview);
    }

    public function store(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId(), 2);

        if (!$checkPermission) {
            return $this->denied();
        }

        $data = $this->validatePayload($request);

        $campaign = null;

        DB::transaction(function () use ($request, $data, &$campaign) {
            $campaign = BulkDiscount::create($data + [
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            $this->syncTargets($campaign, $request);
        });

        $this->resolver->syncCampaign($campaign);

        return redirect()->route('bulk_discount.index')->with('success', 'Bulk discount created successfully.');
    }

    public function edit(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId(), 3);

        if (!$checkPermission) {
            return $this->denied();
        }

        $campaign = BulkDiscount::findOrFail($id);

        $selectedProducts = [];
        if ($campaign->target_type === BulkDiscount::TARGET_PRODUCT) {
            $productIds = DB::table('bulk_discount_products')
                ->where('bulk_discount_id', $campaign->id)
                ->pluck('product_id')
                ->all();

            $selectedProducts = Product::whereIn('id', $productIds)
                ->get(['id', 'name', 'sku', 'regular_price', 'current_price']);
        }

        return Inertia::render('bulk_discount/edit', [
            'campaign' => $campaign,
            'selectedCategoryIds' => DB::table('bulk_discount_categories')
                ->where('bulk_discount_id', $campaign->id)
                ->pluck('category_id'),
            'selectedProducts' => $selectedProducts,
            'categories' => $this->categoryTree(),
            'brands' => Brand::where('status', 1)->get(['id', 'name'])->sortBy('name')->values(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId(), 3);

        if (!$checkPermission) {
            return $this->denied();
        }

        $campaign = BulkDiscount::findOrFail($id);

        $data = $this->validatePayload($request, $campaign);

        // Products the campaign covered before the edit. They must be recomputed
        // too, otherwise products dropped from the campaign keep a stale price.
        $previousProductIds = $this->resolver->productIdsFor($campaign);

        DB::transaction(function () use ($request, $data, $campaign) {
            $campaign->update($data + ['updated_by' => Auth::user()->id]);

            $this->syncTargets($campaign, $request);
        });

        $this->resolver->syncCampaign($campaign->fresh(), $previousProductIds);

        return redirect()->route('bulk_discount.index')->with('success', 'Bulk discount updated successfully.');
    }

    /**
     * End a running campaign now, keeping the record.
     *
     * Deleting an active campaign would lose the history of why prices were
     * what they were, so the row stays and the window is closed instead.
     */
    public function endNow(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId(), 3);

        if (!$checkPermission) {
            return $this->denied();
        }

        $campaign = BulkDiscount::findOrFail($id);

        $productIds = $this->resolver->productIdsFor($campaign);

        $campaign->update([
            'ends_at' => now(),
            'updated_by' => Auth::user()->id,
        ]);

        $this->resolver->sync($productIds);

        return redirect()->route('bulk_discount.index')->with('success', 'Bulk discount ended.');
    }

    /**
     * Only campaigns that never ran can be deleted outright.
     */
    public function destroy(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId(), 4);

        if (!$checkPermission) {
            return $this->denied();
        }

        $campaign = BulkDiscount::findOrFail($id);

        $productIds = $this->resolver->productIdsFor($campaign);

        // Roll prices back while the campaign still exists, so the ledger can
        // hand each product its original price. Deleting first would strip the
        // campaign from the winner set and strand those rows.
        $campaign->update(['status' => 0]);
        $this->resolver->sync($productIds);

        DB::transaction(function () use ($campaign) {
            DB::table('bulk_discount_categories')->where('bulk_discount_id', $campaign->id)->delete();
            DB::table('bulk_discount_products')->where('bulk_discount_id', $campaign->id)->delete();
            DB::table('bulk_discount_applications')->where('bulk_discount_id', $campaign->id)->delete();
            $campaign->delete();
        });

        return redirect()->route('bulk_discount.index')->with('success', 'Bulk discount deleted successfully.');
    }

    private function validatePayload(Request $request, ?BulkDiscount $campaign = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:0,1',
            'discount' => 'required|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'status' => 'nullable|boolean',
            'description' => 'nullable|string',
        ];

        // Target type is locked once created, so the priority of an existing
        // campaign can never silently change under products already priced.
        if (!$campaign) {
            $rules['target_type'] = 'required|in:category,product';
        }

        $validated = $request->validate($rules);

        $targetType = $campaign ? $campaign->target_type : $validated['target_type'];

        if ($targetType === BulkDiscount::TARGET_CATEGORY) {
            $request->validate([
                'category_ids' => 'required|array|min:1',
                'category_ids.*' => 'required|exists:categories,id',
            ]);
        } else {
            $request->validate([
                'product_ids' => 'required|array|min:1',
                'product_ids.*' => 'required|exists:products,id',
            ]);
        }

        if ((int) $validated['discount_type'] === 0 && (float) $validated['discount'] > 100) {
            return abort(422, 'Percentage discount cannot exceed 100.');
        }

        return [
            'name' => $validated['name'],
            'target_type' => $targetType,
            'priority' => $targetType === BulkDiscount::TARGET_CATEGORY
                ? BulkDiscount::PRIORITY_CATEGORY
                : BulkDiscount::PRIORITY_PRODUCT,
            'discount_type' => $validated['discount_type'],
            'discount' => $validated['discount'],
            'starts_at' => date('Y-m-d H:i:s', strtotime($validated['starts_at'])),
            'ends_at' => date('Y-m-d H:i:s', strtotime($validated['ends_at'])),
            'status' => $request->boolean('status', true) ? 1 : 0,
            'description' => $validated['description'] ?? null,
        ];
    }

    private function syncTargets(BulkDiscount $campaign, Request $request): void
    {
        if ($campaign->target_type === BulkDiscount::TARGET_CATEGORY) {
            DB::table('bulk_discount_categories')->where('bulk_discount_id', $campaign->id)->delete();

            $rows = collect($request->input('category_ids', []))
                ->unique()
                ->map(fn ($categoryId) => [
                    'bulk_discount_id' => $campaign->id,
                    'category_id' => (int) $categoryId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all();

            if ($rows) {
                DB::table('bulk_discount_categories')->insert($rows);
            }

            return;
        }

        DB::table('bulk_discount_products')->where('bulk_discount_id', $campaign->id)->delete();

        $rows = collect($request->input('product_ids', []))
            ->unique()
            ->map(fn ($productId) => [
                'bulk_discount_id' => $campaign->id,
                'product_id' => (int) $productId,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('bulk_discount_products')->insert($chunk);
        }
    }
}
