<?php

namespace App\Http\Controllers\Api;



use App\Models\Admin\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;



class BannerController extends Controller
{

    public function index()
    {
        return response()->json(Banner::orderBy('sequence')->where('status', 1)->get(), 200);
    }
}
