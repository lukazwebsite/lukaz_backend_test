<?php

namespace App\Exports;

use App\Models\Admin\Stock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    WithChunkReading
};

class StockExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldQueue
{
    protected $sizes;
    protected $request;

    public function __construct($sizes, $request)
    {
        $this->sizes = $sizes;
        $this->request = $request;
    }

    /**
     * Query (optimized)
     */
    public function query()
    {

        $request = (object) $this->request;

        $query = Stock::query();

        // ✅ Filters
        $query->when(!empty($request->branch), function ($q) use ($request) {
            $q->where('branch_id', $request->branch['id']);
        });

        $query->when(!empty($request->color), function ($q) use ($request) {
            $q->where('color', $request->color['color']);
        });

        // ✅ Product filter
        $query->whereExists(function ($sub) use ($request) {

            $sub->select(DB::raw(1))
                ->from('products as p')
                ->whereRaw('p.id = stocks.product_id');

            if (!empty($request->name)) {
                $sub->where('p.name', 'LIKE', "%{$request->name}%");
            }

            if (!empty($request->category)) {
                $categoryIds = collect($request->category)->pluck('id')->toArray();

                $sub->join('category_product', 'p.id', '=', 'category_product.product_id')
                    ->whereIn('category_product.category_id', $categoryIds);
            }
        });

        $query->orderBy('sku', 'desc');

        // ✅ Relations
        $query->with([
            'branchs:id,name',
            'product:id,name,sku',
            'product.categories:id,name',
            'media'
        ]);



        return $query;
    }

    /**
     * Headings (dynamic sizes)
     */
    public function headings(): array
    {
        $headers = [
            'SL',
            'Branch',
            'Category',
            'Product Name',
            'Product Code',
            'Photo Link',
            'Color',
            'Size',
            'Regular Price',
            'Current Price',
            'Quantity'
        ];

        return $headers;
    }

    /**
     * Map each row
     */
    public function map($stock): array
    {
        static $index = 0;
        $index++;

        // Categories
        $categories = $stock?->product?->categories ? optional($stock->product->categories)->pluck('name')->implode(', ') : '';

        // Photo link
        $photo = $stock->media?->color_thumbnails != null  ? '=HYPERLINK("'.asset('/products/'.$stock->media?->color_thumbnails).'", "Click here")' : '---';

        // Base row
        $row = [
            $index,
            $stock->branchs->name ?? '',
            $categories,
            $stock->product->name ?? '',
            $stock->product->sku ?? '',
            $photo,
            $stock->color,
            $stock->size,
            $stock->regular_price,
            $stock->current_price,
            $stock->stock ? $stock->stock : 0,
        ];

        return $row;
    }

    /**
     * Chunk size (memory safe)
     */
    public function chunkSize(): int
    {
        return 1000; // can tune (500–2000)
    }
}
