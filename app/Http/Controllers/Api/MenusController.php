<?php

namespace App\Http\Controllers\Api;


use App\Models\Api\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Api\Category;
use Illuminate\Support\Facades\Storage;


class MenusController extends Controller
{

    public function index()
    {
        $menus = Category::where('status', 1)->whereNull('parent_id')->where('isActive', 1)
        ->with('childs', function($q){
            $q->where('isActive', 1);
        })->orderBy('sequence')->get();

        return response()->json($menus, 200);
    }




}
