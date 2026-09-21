<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Api\Transaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Models\Api\Order;
use App\Models\Api\OrderTracking;

class SslCommerzPaymentController extends Controller
{


    public function success(Request $request){

        $data = $request->all();


        $composite = $data['tran_id'] ?? '';
        $trimData = explode(',',$composite);
        $tranId = $trimData[0] ?? null;
        $userId = $trimData[1] ?? null;
        $orderId = $trimData[2] ?? null;

        $bankTranId = $data['bank_tran_id'] ?? null;
        $amount     = isset($data['amount']) ? (float) $data['amount'] : 0.00;

        Transaction::create([
            'order_no'       => $orderId ?? $tranId,
            'transaction_id' => $tranId,
            'user_id'        => $userId,
            'total_amount'   => $amount,
            'debit'          => 0,
            'credit'         => $amount,
            'payment_method' => 'SSLCommerz',
            'payment_id'     => $bankTranId,
            'description'    => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);

            // Order Tracking
            OrderTracking::create([
                'order_no' => $orderId,
                'status_id' => 9,
            ]);

        $checkAmount = Order::where('order_no', $orderId)->first();

        if($checkAmount->grand_total >  $amount){

            $status['payment_status'] = 'Partial';
             $status['status'] = 10;

        }else{
            $status['payment_status'] = 'Paid';
             $status['status'] = 9;
        }



        // Order Status change
        Order::where('order_no', $orderId)->update($status);

        return redirect()->away(env('FRONTEND_URL').'/order-success?order_id='.$orderId.'&transaction_id='.$tranId);

    }

    public function fail(Request $request)
    {
        return redirect()->away(env('FRONTEND_URL'));

    }

    public function cancel(Request $request)
    {


        return redirect()->away(env('FRONTEND_URL'));



    }

    public function ipn(Request $request)
    {
        #Received all the payement information from the gateway
        if ($request->input('tran_id')) #Check transation id is posted or not.
        {

            $tran_id = $request->input('tran_id');

            #Check order status in order tabel against the transaction id or order id.
            $order_details = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->select('transaction_id', 'status', 'currency', 'amount')->first();

            if ($order_details->status == 'Pending') {
                $sslc = new SslCommerzNotification();
                $validation = $sslc->orderValidate($request->all(), $tran_id, $order_details->amount, $order_details->currency);
                if ($validation == TRUE) {
                    /*
                    That means IPN worked. Here you need to update order status
                    in order table as Processing or Complete.
                    Here you can also sent sms or email for successful transaction to customer
                    */
                    $update_product = DB::table('orders')
                        ->where('transaction_id', $tran_id)
                        ->update(['status' => 'Processing']);

                    echo "Transaction is successfully Completed";
                }
            } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {

                #That means Order status already updated. No need to udate database.

                echo "Transaction is already successfully Completed";
            } else {
                #That means something wrong happened. You can redirect customer to your product page.

                echo "Invalid Transaction";
            }
        } else {
            echo "Invalid Data";
        }
    }
}
