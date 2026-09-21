<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin\Additional;
use App\Models\InternationalOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternationalOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'full_name' => 'required|string|max:255',
            'whats_app_no' => 'required|string|max:255',
            'full_address' => 'required|string|max:555',
            'country_id' => 'required|numeric',
            'item_id' => 'required|numeric',
            'color' => 'required|max:255',
            'size' => 'required|max:255'
        ]);

        $getItemInfo = Additional::where('product_id', $request->item_id)->where('color', $request->color)->where('size', $request->size)->first();

        if(!empty($getItemInfo)){
            $order['regular_price']   = $getItemInfo->regular_price;
            $order['current_price']   = $getItemInfo->current_price;

            if ((int)$getItemInfo->discount_type === 1) {
                $discount = (float)$getItemInfo->discount;
            } else {
                $discount = round(($getItemInfo->regular_price * ((float)$getItemInfo->discount)) / 100, 2);
            }

            $order['discount_amount'] = $discount;
        }

        $orderNo = $this->getUniqueOrderNo();

        $order['order_no']       = $orderNo;
        $order['full_name']      = $request->full_name;
        $order['whats_app_no']   = $request->whats_app_no;
        $order['full_address']   = $request->full_address;
        $order['country_id']     = $request->country_id;
        $order['item_id']        = $request->item_id;
        $order['color']          = $request->color;
        $order['size']           = $request->size;
        $order['payment_method'] = $request->payment_method;
        $order['brand_id']       = $request->brand_id;
        $order['item_name']      = $request->item_name;
        $order['icon']           = $request->icon;
        $order['item_slug']      = $request->slug;
        $order['quantity']       = $request->quantity;
        $order['email']          = $request->email;
        $order['note']           = $request->note;


        $success = InternationalOrder::create($order);

        return response()->json([
            'message' => 'Order placed successfully',
            'order_no' => $orderNo,
            'order' => $success
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }



    /**
     * Get Account Details
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function getUniqueOrderNo()
    {
        $orderNo = 'LSI-' . rand(1000000,9999999);
        $check = InternationalOrder::where("order_no", $orderNo)->count();
        if($check){
            $this->getUniqueOrderNo();
        }
        return $orderNo;

    }
}
