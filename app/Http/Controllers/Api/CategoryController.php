<?php

namespace App\Http\Controllers\Api;

use App\Models\Api\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Api\ProductAdditionalGallery;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::select('id','parent_id','name','slug','icon','thumbnail','banner','menuImage','featured','sequence','description')
        ->where('status', 1)
        ->where('isActive', 1)
        ->orderBy('sequence', 'asc')
        ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'message' => 'No categories found',
                'data'    => []
            ], 200);
        }

        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data'    => $categories
        ], 200);
    }


    /*
    * Get only featured categories
    */
    public function featured()
    {
        $categories = Category::select('id','parent_id','name','slug','icon','thumbnail','banner','menuImage','featured','sequence','description')
        ->where('status', 1)
        ->where('isActive', 1)
        ->where('featured', 1)
        ->orderBy('sequence', 'asc')
        ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'message' => 'No categories found',
                'data'    => []
            ], 200);
        }

        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data'    => $categories
        ], 200);
    }


    /*
    * Get categories with their child categories recursively
    */
    public function withChilds()
    {
        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->where('isActive', 1)
            ->with(['childs' => function($query) {

                $query->where('status', 1)
                    ->where('isActive', 1)
                    ->orderBy('sequence', 'asc')
                ->with(['childs' => function($query) {
                        $query->where('status', 1)
                    ->where('isActive', 1)
                    ->orderBy('sequence', 'asc');
                }]); // Eikhane 'childs' model-er method-ti call hobe
            }])
            ->orderBy('sequence', 'asc')
            ->get();



        if ($categories->isEmpty()) {
            return response()->json([
                'message' => 'No categories found',
                'data'    => []
            ], 200);
        }

        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data'    => $categories
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


        $category = Category::where('slug', $slug)->with('products:id')->first();

        $limiPerPage = isset($request->per_page) ? $request->per_page : 10;

        $append = $request->all();

        $addintionlGalleries = $category
            ? ProductAdditionalGallery::whereIn('product_id', $category->products->pluck('id'))
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('products')
                        ->whereColumn('products.id', 'product_additional_gallaries.product_id')
                        ->where('products.status', 1);
                })
                ->with('product', function($q){
                    $q->where('status', 1);
                })
                ->paginate($limiPerPage)->appends($append)->withPath('/api/categories/'.$slug.'/with/products')
            : collect();



        if (!$category) {
            return response()->json([
                'message' => 'Category not found',
                'category'    => $category,
                'data'    => null
            ], 404);
        }

        return response()->json([
            'message' => 'Category retrieved successfully',
            'category'    => $category,
            'products'    => $addintionlGalleries
        ], 200);

     }

}
