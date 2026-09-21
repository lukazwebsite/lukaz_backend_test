<?php

namespace App\Exports;

use App\Models\Admin\Stock;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductSaleExport implements FromQuery, WithMapping, WithHeadings, WithChunkReading
{

    protected $request;
    protected $limit;

    public function __construct($request, $limit = null)
    {
        $this->request = $request;
        $this->limit = $limit;
    }

    public function query()
    {
        $request = $this->request;

        $query = Stock::where(function($q) use ($request){

            if (!empty($request->name)) {
                $q->whereHas('product', function($q) use($request){
                    $q->where('name', 'LIKE', '%'.$request->name.'%');
                });
            }

            $q->whereHas('orderitems', function($q) use($request){

                if ($request->fromDate && $request->toDate) {
                    $q->whereBetween('created_at', [
                        $request->fromDate.' 00:00:00',
                        $request->toDate.' 23:59:59'
                    ]);
                }

                if ($request->branch) {
                    $q->where('branch_id', $request->branch['id']);
                }

                if ($request->brand) {
                    $q->where('brand_id', $request->brand['id']);
                }

            });

        })
        ->with([
            'product.brand',
            'product.categories',
            'orderitems.order.createdBy',
            'media',
            'branchs'
        ])
        ->withSum('orderitems', 'quantity');

        return $query->limit($this->limit); // ✅ only 20%
    }

    public function map($sale): array
    {
        $orderType = [0 => 'Pre-order', 1 => 'Online', 2 => 'Pos']
        [$sale?->orderitems?->order?->order_type] ?? 'Manual';
        return [
            $sale->id,
            $sale->orderitems?->order_no,
            $sale?->media?->color_thumbnails  ? '=HYPERLINK("'.asset('/products/'.$sale->media->color_thumbnails).'", "Click here")' : '',
            $sale?->product?->name,
            $sale?->product?->brand?->name,
            $sale?->product?->categories->pluck('name')->implode(', '),
            $sale?->size,
            $sale?->color,
            $sale?->branchs?->name,
            $orderType,
            $sale?->orderitems?->order?->createdBy->name,
            $sale->orderitems_sum_quantity ?? 0,
            $sale->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'SL',
            'Order No',
            'Icon',
            'Product Name',
            'Brand',
            'Category',
            'Size',
            'Color',
            'Branch',
            'Sale Type',
            'Saled By',
            'Sales',
            'Date Time'
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
