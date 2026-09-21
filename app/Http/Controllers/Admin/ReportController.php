<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductSaleExport;
use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Stock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\Additional;
use App\Models\Admin\Branch;
use App\Models\Admin\Brand;
use App\Models\Admin\Category;
use App\Models\Api\Order;
use App\Models\Api\ProductAdditionalGallery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Excel;

class ReportController extends Controller
{
    private $menuId = 38;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Excel $excel)
    {

        $checkPermission = Permission::access($request, $this->menuId, 1);

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }


        $menuAccess = AccessUser::where('user_id', Auth::user()->id)->where('menu_id', $this->menuId)->get([
            'user_id',
            'menu_id',
            'action_id'
        ]);


        $append = [];

        $query = Stock::where(function($q) use($request){

            if (!empty($request->name)) {
                $q->whereHas('product', function($q) use($request){
                    $q->where('name', 'LIKE', '%'.$request->name.'%');
                });
            }


            $q->whereHas('orderitems', function($q) use($request){
                if ( $request->has('fromDate') && $request->fromDate !== null && $request->has('toDate') && $request->toDate !== null) {
                    $q->whereBetween('created_at', [ $request->fromDate . ' 00:00:00', $request->toDate . ' 23:59:59' ]);
                }
                if($request->has('fromDate') && $request->fromDate !== null && !$request->has('toDate')) {
                    $q->whereBetween('created_at', [ $request->fromDate . ' 00:00:00', Carbon::now() ]);
                }

                if ($request->has('branch') && $request->branch !== null) {
                    $q->where('branch_id', $request->branch['id']);
                }

                if($request->brand != ''){
                    $q->where('brand_id', $request->brand['id']);
                }

                if (!empty($request->color)) {
                    $q->where('color', 'LIKE', '%'.$request->color['color'].'%');
                }

                if (!empty($request->size)) {
                    $q->where('size', 'LIKE', '%'.$request->size['size'].'%');
                }


                if($request->staff != '' || $request->type != '' ){

                    $q->whereHas('order', function($q) use($request){
                        if($request->staff != ''){
                            $q->where('created_by', $request->staff['id']);
                        }

                        if($request->type != ''){
                            $q->where('order_type', $request->type['id']);
                        }


                    });
                }

            });

        })
        ->with(['product.categories', 'orderitems', 'product', 'product.brand', 'orderitems.order', 'media', 'branchs', 'orderitems.order.createdBy'])

        ->withSum(['orderitems' => function ($q) use ($request) {

            if ( $request->has('fromDate') && $request->fromDate !== null && $request->has('toDate') && $request->toDate !== null) {
                $q->whereBetween('created_at', [
                    $request->fromDate . ' 00:00:00',
                    $request->toDate . ' 23:59:59'
                ]);

            }else if($request->has('fromDate') && $request->fromDate !== null && !$request->has('toDate')) {
                $q->whereBetween('created_at', [
                    $request->fromDate . ' 00:00:00',
                    Carbon::now()
                ]);
            }

        }], 'grand_total');

        if($request->has('category') && $request->category !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('products as p')
                    ->whereRaw('stocks.product_id = p.id')
                    ->whereJsonContains('category_ids', $request->category['id']);
            });

        }







        $categories = Category::where('status', 1)->get(['id', 'name']);
        $brands = Brand::where('status', 1)->get(['id', 'name']);

        $colors = Additional::distinct()->get('color');
        $sizes = Additional::distinct()->get('size');
        $branches = Branch::get(['id', 'name']);
        $staffs = User::where('status', 1)->where('role_id', '<=', 4)->get(['id', 'name']);


        $append = array_filter([
            'staff'    => $request->staff,
            'type'     => $request->type,
            'fromDate' => $request->fromDate,
            'toDate'   => $request->toDate,
            'name'     => $request->name,
            'color'    => $request->color,
            'size'     => $request->size,
            'brand'    => $request->brand,
            'category' => $request->category,
            'branch'   => $request->branch,
            'report'   => $request->report,

        ]);


        $total = $query->count();

        if($request->excel == "download"){

            $limit = ceil($total * 0.2);

            return $excel->download(new ProductSaleExport($request, $limit), 'Sales_Reporst_'.date("j_m_Y_h_i_s", strtotime(Carbon::now())).'.xlsx');
        }


        $data = $query->orderBy('orderitems_sum_grand_total', 'asc')->take(ceil($total * 0.2))->get();


        $perPage = 15; // Number of items per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $items = $data->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $stocks = new LengthAwarePaginator(
            $items,
            $data->count(), // total = 20
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );


        return Inertia::render('report/index', [
            'menuAccess' => $menuAccess,
            'categories' => $categories,
            'brands' => $brands,
            'colors' => $colors,
            'sizes' => $sizes,
            'stocks' => $stocks,
            'branches' => $branches,
            'staffs' => $staffs,
            'append' => $append,
        ]);




    }


    /**
     * Show the form for creating a new resource.
     */
    public function sale(Request $request, Excel $excel)
    {


        $sizes = Additional::distinct()->get('size');
        $colors = Additional::distinct()->get('color');
        $branches = Branch::get(['id', 'name']);
        $categories = Category::where('status', 1)->get(['id', 'name']);

        $toDate = isset($request->toDate)  ? date('Y-m-d', strtotime($request->toDate)) : date('Y-m-d', strtotime(Carbon::now()->endOfMonth()));
        $fromDate = isset($request->fromDate)  ? date('Y-m-d', strtotime($request->fromDate)) : date('Y-m-d', strtotime(Carbon::now()->startOfMonth()));

        $append = $request->all();

        $append['fromDate'] = $fromDate;
        $append['toDate'] = $toDate;

        $categoriesIds = !empty($append['categories']) ? collect($append['categories'])->pluck('id')->toArray() : [];

        $query = Order::with(['items', 'items.additional.product', 'items.additional.product.categories'])
            ->withCount('items')
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->whereHas('items', function ($itemQuery) use ($request, $categoriesIds) {
                // Branch Filter (Removed the duplicate!)
                $itemQuery->when(!empty($request->branch), function ($q) use ($request) {
                    $q->where('branch_id', $request->branch['id']);
                });

                // Sizes Filter
                $itemQuery->when(!empty($request->sizes), function ($q) use ($request) {
                    $q->where('size', $request->sizes['size']);
                });

                // Name/SKU Filter
                $itemQuery->when(!empty($request->name), function ($q) use ($request) {
                    $q->whereHas('additional.product', function ($productQuery) use ($request) {
                        $productQuery->whereAny(['name', 'sku'], 'LIKE', '%' . $request->name . '%');
                    });
                });

                // Categories Filter
                $itemQuery->when(!empty($categoriesIds), function ($q) use ($categoriesIds) {
                    $q->whereHas('additional.product.categories', function ($categoryQuery) use ($categoriesIds) {
                        $categoryQuery->whereIn('category_id', $categoriesIds);
                    });
                });

            })
            ->orderBy('grand_total', 'desc');

            $total = $query->count();

            if($request->excel == "download"){

                $sales = $query->take(ceil($total * 0.2))->get();

                return $excel->download(new SalesExport($sales), 'Sales_&_Order_Reporst_'.date("j_m_Y_h_i_s", strtotime(Carbon::now())).'.xlsx');
            }


            $data = $query->take(ceil($total * 0.2))->get();


            $perPage = 15; // Number of items per page
            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            $items = $data->slice(($currentPage - 1) * $perPage, $perPage)->values();

            $sales = new LengthAwarePaginator(
                $items,
                $data->count(), // total = 20
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );




        return Inertia::render('report/sale', [
            'sales' => $sales,
            'sizes' => $sizes,
            'colors' => $colors,
            'branches' => $branches,
            'categories' => $categories,
            'append' => $append,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function stocks(Request $request)
    {
        $sizes = Additional::distinct()->pluck('size')->toArray();
        $colors = Additional::distinct()->get('color');
        $branches = Branch::get(['id', 'name']);
        $categories = Category::where('status', 1)->get(['id', 'name']);

        $append = $request->all();


        // dd($request->all());


        $query = Stock::select('branch_id', 'product_id', 'color', 'regular_price', 'current_price', 'slug');

        foreach($sizes as $size){

            $col  = preg_replace('/[^a-zA-Z0-9]/', '_', $size);
            // quote the size value in SQL
            $query->selectRaw(
                "SUM(CASE WHEN REPLACE(TRIM(size),' ','') = ? THEN stock ELSE 0 END) AS size_$col",
                [$size]
            );

        }

        $branchWiseStocks = $query

            ->where(function($q) use($request){
                if(isset($request->branch) && !empty($request->branch)){
                    $q->where('branch_id', $request->branch['id']);

                }
            })

            ->where(function($q) use($request){
                if(isset($request->color) && !empty($request->color)){
                    $q->where('color', $request->color['color']);
                }
            })

            ->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1), 'stock as product_stock')
                    ->from('products as p')
                    ->whereRaw('p.id = stocks.product_id');


                if (!empty($request->name)) {
                    $sub->where('name', 'LIKE', "%$request->name%");
                }

                if (!empty($request->category)){

                    $categoryIds = collect($request->category)->pluck('id')->toArray();
                    $sub->join('category_product', 'p.id', '=', 'category_product.product_id')
                    ->whereIn('category_product.category_id', $categoryIds);
                }


            })

            ->with(['branchs', 'product', 'product.categories', 'media'])

            ->groupBy('branch_id', 'product_id', 'color', 'regular_price', 'current_price', 'slug')
            ->paginate()->withQueryString();



        return Inertia::render('report/stocks', [
            'append' => $append,
            'sizes' => $sizes,
            'colors' => $colors,
            'branches' => $branches,
            'categories' => $categories,
            'branchWiseStocks' => $branchWiseStocks,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function StockExports(Request $request,  Excel $excel)
    {
        $sizes = Additional::distinct()->pluck('size')->toArray();

        // $query = Stock::select('branch_id', 'product_id', 'color', 'regular_price', 'current_price', 'slug');

        // foreach($sizes as $size){
        //     $col  = preg_replace('/[^a-zA-Z0-9]/', '_', $size);
        //     // quote the size value in SQL
        //     $query->selectRaw(
        //         "SUM(CASE WHEN REPLACE(TRIM(size),' ','') = ? THEN stock ELSE 0 END) AS size_$col",
        //         [$size]
        //     );
        // }

        // $branchWiseStocks = $query

        //     ->where(function($q) use($request){
        //         if(isset($request->branch) && !empty($request->branch)){
        //             $q->where('branch_id', $request->branch['id']);

        //         }
        //     })

        //     ->where(function($q) use($request){
        //         if(isset($request->color) && !empty($request->color)){
        //             $q->where('color', $request->color['color']);
        //         }
        //     })

        //    ->whereExists(function($sub) use($request){
        //         $sub->select(DB::raw(1), 'stock as product_stock')
        //             ->from('products as p')
        //             ->whereRaw('p.id = stocks.product_id');


        //         if (!empty($request->name)) {
        //             $sub->where('name', 'LIKE', "%$request->name%");
        //         }

        //         if (!empty($request->category)){

        //             $categoryIds = collect($request->category)->pluck('id')->toArray();
        //             $sub->join('category_product', 'p.id', '=', 'category_product.product_id')
        //             ->whereIn('category_product.category_id', $categoryIds);
        //         }


        //     })

        //     ->with(['branchs', 'product', 'product.categories', 'media'])

        //     ->groupBy('branch_id', 'product_id', 'color', 'regular_price', 'current_price', 'slug')
        //     ->get();

        $sizes = Additional::distinct()->pluck('size')->toArray();

        return $excel->download(
            new StockExport($sizes, $request->all()), 'Stock_Reports_'.date("j_m_Y_h_i_s", strtotime(Carbon::now())).'.xlsx'
        );
    }


}
