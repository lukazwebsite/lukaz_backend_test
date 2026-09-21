<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;

use App\Models\Api\Product;
use Illuminate\Http\Request;
use App\Models\Admin\Additional;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Api\ProductAdditionalGallery;

class ProductController extends Controller
{

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {

        $per_page = ($request->per_page) ? $request->per_page : 20;
        $orderlowToHight = null;
        if(isset($request->sort_by_price) && $request->sort_by_price == "high_to_low") {
            $orderlowToHight = "DESC";
        };

        if(isset($request->sort_by_price) && $request->sort_by_price == "low_to_high") {
            $orderlowToHight = "ASC";
        };

        $orderBy = ($request->low_to_high) ? "ASC" : null;

        $products = DB::table('product_additional_gallaries as pg')
        ->leftJoin('products as p', 'p.id', '=', 'pg.product_id')
        ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
        ->when(request('search'), function($q, $search) {
            $q->where('p.name', 'like', '%' . $search . '%');
        })
        ->when(request('international'), function($q, $international) {
            $q->where('p.international', $international);
        })
        ->when(request('brand_id'), function($q, $brand) {
            $q->where('p.brand_id', $brand);
        })
        ->when(request('size'), function($q, $size) {
            $q->whereRaw('JSON_CONTAINS(p.size, json_quote(?))', [$size]);
        })
        ->when(request('category_id'), function($q, $category) {
            $q->whereJsonContains('p.category_ids', explode(',', $category));
        })
        ->when(request('color'), function($q, $color) {
            $q->where('pg.color', $color);
        })
        ->when(request('sort_by_price'), function($q, $order) use($orderlowToHight) {
            $q->orderBy('p.current_price', $orderlowToHight);
        })

        ->where('p.status', 1)
        ->select('pg.*', 'b.name as brand_name', 'b.slug as brand_slug', 'p.name as product_name', 'p.discount',  'p.discount_type', 'p.brand_id', 'p.regular_price', 'p.current_price', 'p.slug as product_slug', 'p.id as product_id', 'p.color as product_color', 'p.size as product_size')
        ->paginate($per_page)
        ->appends(request()->query());




        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found',
                'data'    => []
            ], 200);
        }

        return response()->json([
            'message' => 'Products retrieved successfully',
            'data'    => $products
        ], 200);
    }



    /**
     * Display the specified resource.
     * @param  int  $slug
     * @method GET
     * @return \Illuminate\Http\Response
     */

    public function show($slug)
    {

        $products = ProductAdditionalGallery::with([
            'product',
            'product.brand',
            'product.categories',
            'product.relatedProducts',
            'gallaries',
            'review',
            'additionals' => function($q) {
                $q->withSum(['stocks' => function($query) {
                   $query->whereHas('branches', function($branchQuery) {
                        $branchQuery->where('is_default', '!=', 1);
                    });
                }], 'stock');
            }
        ])->withCount('review')
            ->withAvg('review', 'rating')
            ->where('slug',  $slug)
        ->get();


        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found',
                'data'    => []
            ], 200);
        }

        // Return products data
        return response()->json([
            'message' => 'Products retrieved successfully',
            'data'    => $products
        ], 200);
    }
}
