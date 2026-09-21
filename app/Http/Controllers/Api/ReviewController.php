<?php

namespace App\Http\Controllers\Api;

use App\Models\Api\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{


    public function index(Request $request)
    {

            $data['reviews'] = Review::with('user:id,name')->where('status', 1)->latest()->limit(10)->get();
            $data['all_rattings'] = Review::where('status', 1)->sum('rating');
            $data['avarage'] = Review::where('status', 1)->avg('rating');

            return response()->json([
                'message' => 'Reviews fetched successfully',
                'data'    => $data
            ], 200);
    }


    public function store(Request $request)
    {


        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'title'      => 'required|string|max:255',
            'review'     => 'required|string',
        ]);


        $check = Review::where('product_id', $request->product_id)->where('user_id', $request->user()->id)->count();

        if($check){
            return response()->json([
                'message' => 'You are not allow multiple review this product',
                'error'   => "exits"
            ], 500);
        }


        try {

            $data['user_id']    = $request->user()->id;
            $data['product_id'] = $request->product_id;
            $data['rating']     = $request->rating;
            $data['title']      = $request->title;
            $data['review']     = $request->review;
            $data['status']     = 0; // Pending approval

            $review = Review::create($data);

            return response()->json([
                'message' => 'Review submitted successfully',
                'data'    => $review
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while submitting review',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


}
