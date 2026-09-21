<?php

namespace App\Http\Controllers\Api;

use App\Models\Api\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Branch;
use App\Models\Admin\Brand;
use App\Models\Api\ProductAdditionalGallery;

class OutlateController extends Controller
{

    /**
     * Show the products form thier won category slug
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {

        $brands = Branch::where('status', 1);
        if(isset($request->limit) && $request->limit){
            $brands = $brands->limit($request->limit)->get();
        }else if(isset($request->per_page) && $request->per_page){
            $brands = $brands->paginate($request->per_page);
        }else{
            $brands = $brands->get();
        }

        if ($brands->isEmpty()) {
            return response()->json([
                'message' => 'No Outlet found',
                'data'    => []
            ], 200);
        }

        return response()->json([
            'message' => 'Outlet retrieved successfully',
            'data'    => $brands
        ], 200);
    }


}
