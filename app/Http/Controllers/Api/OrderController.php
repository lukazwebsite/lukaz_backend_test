<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Api\Order;
use App\Models\Api\Coupon;
use Illuminate\Support\Str;
use App\Models\Api\PromoUse;
use Illuminate\Http\Request;
use App\Models\Api\OrderItem;
use App\Models\Api\Transaction;
use App\Models\Api\ShippingInfo;
use App\Models\Api\OrderTracking;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Library\SslCommerz\SslCommerzNotification;
use Nette\Utils\Random;

class OrderController extends Controller
{


    /**
     * Order List
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * param string $order_no
     */

    public function index(Request $request)
    {
        $user = $request->user();

        $orderType = isset($request->order_type) ? $request->order_type : 1;

        try{

        $data['orders'] = Order::withCount('items')->where('user_id', $user->id)->orderBy('created_at', 'desc')->where('order_type', $orderType)->paginate(10);

        $data['total_order'] = Order::where('user_id', $user->id)->count();
        $data['total_pending'] = Order::where('user_id', $user->id)->where('status', 1)->where('order_type', 0)->count();
        $data['total_delivered'] = Order::where('user_id', $user->id)->where('status', 8)->where('order_type', 1)->count();
        $data['total_cancel'] = Order::where('user_id', $user->id)->where('status', 6)->where('order_type', 1)->count();
        $data['pre_order'] = Order::where('user_id', $user->id)->where('order_type', 0)->count();

        return response()->json($data, 200);

         } catch (\Exception $e) {
            return response()->json([
                'message' => 'You are not authorized to view this order.',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
                'message' => 'You are not authorized to view this order.',
                'error' => 'Please login to view your orders.'
            ], 401);
    }


    /**
     * Order details
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * param string $order_no
     */

    public function show(Request $request, $orderNo)
    {
        $user = $request->user();

        try {

            $order = Order::where('order_no', $orderNo)
                // ->where('user_id', $user->id)
                ->with(['items', 'shippingInfo', 'transactions'])
                ->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found or you are not authorized to view this order.'], 404);
            }

            return response()->json($order, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'You order not found or you are not authorized to view this order.',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
                'message' => 'You are not authorized to view this order.',
                'error' => 'Please login to view your orders.'
            ], 401);

    }

    /**
     * Place Order
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function  placeOrder(Request $request)
    {

        DB::beginTransaction();

        try {

            $user = $request->user();

            // dd($request->user());

            // Check user login or not
            if(empty($request->user())){
                $user = User::where('mobile', $request->shipping['phone'])->first();

                if ($user) {
                    $user->tokens()->delete();
                    $token =   $user->createToken($request->shipping['phone'])->plainTextToken;
                } else {
                    $user = User::create([
                        'name' => $request->shipping['full_name'],
                        'email' => $request->shipping['phone'] . '@example.com',
                        'mobile' => $request->shipping['phone'],
                        'password' => Hash::make('12345678'),
                    ]);

                    $token =  $user->createToken($request->shipping['phone'])->plainTextToken;
                }
            }

            $userID = $user->id;
            $orderNo = 'LS-' . $this->getUniqueOrderNo('orders', 'order_no');

            $totalQty = 0;
            $subTotal = 0.0;

            $orderItem = [];

            foreach ($request->items as $it) {
                unset($it['partial']);
                $lineTotal = ($it['current_price'] ?? 0) * ($it['quantity'] ?? 0);
                $subTotal += $lineTotal;
                $totalQty += $it['quantity'];
                $orderItem[] = array_merge([
                    'created_at' => now(),
                    'updated_at' => now(),
                    'order_no' => $orderNo,
                    'additional_key' => Str::slug($it['item_id'].'_'.$it['color'].'_'.$it['size'], '_'),
                ], $it, [
                    'grand_total' =>  $lineTotal
                ]);
            }

            $shippingCost = (float)($request->shipping_cost ?? 0);


            $discount = 0.0;
            $appliedCoupon = null;
            if (!empty($request->promo_code)) {
                $appliedCoupon = Coupon::query()
                    ->where('slug', $request->promo_code)
                    ->where(function ($query) {
                        $query->whereDate('start_date', '<=', now())
                            ->orWhereDate('end_date', '>=', now());
                    })
                    ->where('status', 1)
                    ->first();

                if (!$appliedCoupon) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Invalid or inactive promo code.'
                    ], 422);
                }

                $userUsed = PromoUse::where('user_id', $userID)->where('promo_id', $appliedCoupon->id)->count();



                if ($userUsed > 0 && $appliedCoupon->limit != 0) {
                    DB::rollBack();
                    return response()->json(['message' => 'This promo code has already been used by the user.'], 422);
                }


                $couponUsageCount = PromoUse::where('promo_id', $appliedCoupon->id)->count();

                if ($appliedCoupon->limit != 0 && $couponUsageCount >= $appliedCoupon->limit) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Promo code usage limit reached.'
                    ], 422);
                }

                if ((int)$appliedCoupon->discount_type === 1) {
                    $discount = (float)$appliedCoupon->discount;
                } else {
                    $discount = round(($subTotal * ((float)$appliedCoupon->discount)) / 100, 2);
                }

                $discount = min($discount, $appliedCoupon->max_discount);

                $discount = min($discount, $subTotal);
            }

            $grandTotal = max(0, $subTotal + $shippingCost - $discount);



            Order::create([
                'order_no' => $orderNo,
                'user_id' => $userID ?? null,
                'status' => 1, // Pending
                'order_type' => (int)($request->order_type ?? 1),
                'discount' => $discount,
                'quantity' => $totalQty,
                'shipping_cost' => $shippingCost,
                'total' => $subTotal,
                'grand_total' => $grandTotal,
                'payment_status' => 'Due',
                'promo_code' => $request->promo_code ?? null,
                'description' => $request->description ?? null,
                'created_by' => $userID ?? null,
            ]);


            // Bulk insert Order Items
            OrderItem::insert($orderItem);


            // Shipping Information
            ShippingInfo::create([
                'order_no' => $orderNo,
                'district_id' => $request->shipping['district_id'],
                'thana' => $request->shipping['thana'],
                'full_name' => $request->shipping['full_name'],
                'phone' => $request->shipping['phone'],
                'address' => $request->shipping['address'],
                'note' => $request->shipping['note'] ?? null,
            ]);

            // Order Tracking
            OrderTracking::create([
                'order_no' => $orderNo,
                'status_id' => 1,
            ]);

            // Apply Promo Use
            if ($appliedCoupon) {
                PromoUse::create([
                    'user_id' => $userID ?? null,
                    'promo_id' => $appliedCoupon->id,
                    'order_no' => $orderNo,
                ]);
            }

            // full payment option here for bkash
            if ($request->payment_method == 'bkash' && $request->payment_type == "others") {
                DB::commit();
                $data = [];
                $data['order_no'] = $orderNo;
                $data['amount'] = $grandTotal;
                $data['user_id'] = $userID;
                $return['payment_url'] = $this->bKashPaymentRequest($request->merge($data));
                $return['token'] = empty($request->user()) ? $token : null;
                $return['user'] = $user;
                return $return;
            }

            // full payment option here for SSLCommerz
            if ($request->payment_method == 'sslcommerz' && $request->payment_type == "others") {
                DB::commit();
                $data = [];
                $data['order_no'] = $orderNo;
                $data['total_amount'] = $grandTotal;
                $data['user_id'] = $userID ?? null;
                $data['tran_id'] = $this->getUniqueOrderNo('orders', 'order_no');

                $return['payment_url'] = $this->sslCommerzPaymentRequest($request->merge($data));
                $return['token'] = empty($request->user()) ? $token : null;
                $return['user'] = $user;
                return $return;
            }


            // partial payment option here for bkash
            if ($request->payment_method == 'bkash' && $request->payment_type == 'partial') {
                DB::commit();

                $items = collect($request->items);
                $maxAmount = $items->sortByDesc('partial')->first();

                $data = [];
                $data['order_no'] = $orderNo;
                $data['amount'] = ($maxAmount['partial'] > $request->partial_input_amount) ? $maxAmount['partial'] : $request->partial_input_amount;
                $data['user_id'] = $userID;
                $return['payment_url'] = $this->bKashPaymentRequest($request->merge($data));
                $return['token'] = empty($request->user()) ? $token : null;
                $return['user'] = $user;
                return $return;
            }


            // partial payment option here for SSLCommerz
            if ($request->payment_method == 'sslcommerz' && $request->payment_type == 'partial') {
                DB::commit();
                $items = collect($request->items);
                $maxAmount = $items->sortByDesc('partial')->first();

                $data = [];
                $data['order_no'] = $orderNo;
                $data['total_amount'] = ($maxAmount['partial'] > $request->partial_input_amount) ? $maxAmount['partial'] : $request->partial_input_amount;
                $data['user_id'] = $userID ?? null;
                $data['tran_id'] = $this->getUniqueOrderNo('orders', 'order_no');

                $return['payment_url'] = $this->sslCommerzPaymentRequest($request->merge($data));
                $return['token'] = empty($request->user()) ? $token : null;
                $return['user'] = $user;
                return $return;
            }



            // Transaction Record
            $txnId = $this->getUniqueOrderNo('transactions', 'transaction_id');
            $paid = false;

            Transaction::create([
                'order_no' => $orderNo,
                'transaction_id' => $txnId,
                'user_id' => $userID ?? 0,
                'total_amount' => $grandTotal,
                'debit' => $paid ? 0 : $grandTotal,
                'credit' => $paid ? $grandTotal : 0,
                'payment_method' => $request->payment_method,
                'payment_id' => null,
                'description' => $paid ? 'Paid' : 'Due',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully',
                'order_no' => $orderNo,
                'token' => empty($request->user()) ? $token : null,
                'user' => $user
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['message' => 'Error placing order', 'error' => $e->getMessage()], 500);
        }
    }


    public function getDiscountForPromoCode(Request $request)
    {

        $validated = $request->validate([
            'promo_code' => ['required', 'string']
        ]);

        try {

            $appliedCoupon = Coupon::where('slug', $validated['promo_code'])
                ->where('status', 1)
                ->where(function ($query) {
                    $query->whereDate('start_date', '<=', now())
                        ->orWhereDate('end_date', '>=', now());
                })
                ->first();

            if (!$appliedCoupon) {
                return response()->json([
                    'message' => 'Invalid or inactive promo code.'
                ], 422);
            }

            $couponUsageCount = PromoUse::where('promo_id', $appliedCoupon->id)->count();

            if ($appliedCoupon->limit != 0 && $couponUsageCount >= $appliedCoupon->limit) {
                return response()->json([
                    'message' => 'Promo code usage limit reached.'
                ], 422);
            }

            return response()->json([
                'discount_type' => $appliedCoupon->discount_type,
                'discount' => $appliedCoupon->discount,
                'max_discount' => $appliedCoupon->max_discount,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error calculating discount.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bKashPaymentRequest(Request $request)
    {
        $bKash = new BkashController();
        $token = $bKash->grant();
        $data['token'] = $token;
        $data['order_no'] = $request->order_no;
        $data['amount'] = $request->amount;
        $data['user_id'] = $request->user_id;
        $response = $bKash->create($request, $data);
        // $url['payment_url'] = $response->bkashURL ?? '';
        return  $response->bkashURL ?? '';
    }

    public function sslCommerzPaymentRequest(Request $request)
    {

        $post_data = array();
        $post_data['total_amount'] = $request->total_amount;
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = $request->tran_id . ',' . $request->user_id . ',' . $request->order_no;
        $post_data['shipping_info'] = $request->shipping;

        $sslc = new SslCommerzNotification();

        $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');

        $getData = json_decode($payment_options, true);
        //  $url['payment_url'] = $getData['data'] ?? '';
        return $getData['data'] ?? '';
    }


    /**
     * Order Tracking
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * param string $order_no
     */

    public function orderTracking(Request $request)
    {
        $request->validate([
            'order_no' => 'required|string',
            'phone' => 'required|string',
        ]);

        $tracking = OrderTracking::where('order_no', $request->order_no)
            ->whereHas('shippingInfo', function ($query) use ($request) {
                $query->where('phone', $request->phone);
            })
            ->get();

        return response()->json($tracking, 200);
    }


    /**
     * Order Traces
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * param string $order_no
     * param string $phone
     */

    public function traces(Request $request)
    {
        $request->validate([
            'order_no' => 'required|string',
            'phone' => 'required|string',
        ]);

        $order = OrderTracking::where('order_no', $request->order_no)
            ->whereHas('shippingInfo', function ($query) use ($request) {
                $query->where('phone', $request->phone);
            })
            ->get();

        if (!$order) {
            return response()->json(['message' => 'Order not found or phone number does not match.'], 404);
        }

        return response()->json($order, 200);
    } // end of



    /**
     * Get Account Details
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function accountDetails(Request $request)
    {
        $user = $request->user()->load('shippingInfo');


        return response()->json([
            'profile_information' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->mobile,
            ],
            'shipping_info' => $user->shippingInfo
        ], 200);
    }


     /**
     * Get Account Details
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function getUniqueOrderNo($table, $column)
    {
        $orderNo = rand(1000000,9999999);
        $check = DB::table($table)->where($column, $orderNo)->count();
        if($check){
            $this->getUniqueOrderNo($table, $column);
        }
        return $orderNo;

    }



}
