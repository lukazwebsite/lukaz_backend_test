<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\ShopBy;

class ShopByController extends Controller
{
    public function index()
    {
        $shopBys = ShopBy::get();
        return response()->json($shopBys, 200);
    }
}
