<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Admin\VideoFeature;
use App\Http\Controllers\Controller;

class VideoFeatureController extends Controller
{
     public function index()
    {
        return response()->json(VideoFeature::get(), 200);
    }
}
