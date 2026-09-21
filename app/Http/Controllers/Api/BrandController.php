<?php

namespace App\Http\Controllers\Api;

use App\Models\Api\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Brand;
use App\Models\Api\ProductAdditionalGallery;

class BrandController extends Controller
{
    public function index(Request $request)
    {

        $brands = Brand::select('id','name','slug','icon','thumbnail','banner','description')
        ->where('status', 1);
        if(isset($request->limit) && $request->limit){
            $brands = $brands->limit($request->limit)->get();
        }else if(isset($request->per_page) && $request->per_page){
            $brands = $brands->paginate($request->per_page);
        }else{
            $brands = $brands->get();
        }

        if ($brands->isEmpty()) {
            return response()->json([
                'message' => 'No Brand found',
                'data'    => []
            ], 200);
        }

        return response()->json([
            'message' => 'Brand retrieved successfully',
            'data'    => $brands
        ], 200);
    }


    /**
     * Show the products form thier won category slug
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */

     public function withProducts(Request $request, $slug)
     {


        $brand = Brand::where('slug', $slug)->first();


        $append = $request->all();

        $addintionlGalleries = $brand
            ? ProductAdditionalGallery::with(['product' => function ($q) use ($brand) {
                $q->where('brand_id', $brand->id)
                ->where('status', 1);
            }])
                ->paginate(10)->appends($append)->withPath('/api/brands/'.$slug.'/with/products')
            : collect();



        if (!$brand) {
            return response()->json([
                'message' => 'Brand not found',
                'brand'    => $brand,
                'data'    => null
            ], 404);
        }

        return response()->json([
            'message' => 'Brand retrieved successfully',
            'brand'    => $brand,
            'products'    => $addintionlGalleries
        ], 200);

     }

}
