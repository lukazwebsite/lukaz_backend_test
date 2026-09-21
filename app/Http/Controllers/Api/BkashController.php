<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Api\Transaction;
use App\Http\Controllers\Controller;
use App\Models\Api\Order;
use App\Models\Api\OrderTracking;
use Illuminate\Support\Facades\Session;

class BkashController extends Controller
{

    private $base_url;

    public function __construct()
    {
        $this->base_url = env("BKASH_CHECKOUT_URL");
    }

    public function authHeaders(Request $request)
    {
        $var = array(
            'Content-Type:application/json',
            'Authorization:' . $this->grant(),
            'X-APP-Key:' . env('BKASH_CHECKOUT_URL_APP_KEY'),
            'X-Amz-Date: ' . now()->toIso8601String()
        );

        return $var;
    }


    public function curlWithBody($url, $header, $method, $body_data_json)
    {
        $curl = curl_init($this->base_url . $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body_data_json);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }


    public function grant()
    {
        $header = array(
            'Content-Type:application/json',
            'username:' . env('BKASH_CHECKOUT_URL_USER_NAME'),
            'password:' . env('BKASH_CHECKOUT_URL_PASSWORD')
        );

        $body_data = array('app_key' => env('BKASH_CHECKOUT_URL_APP_KEY'), 'app_secret' => env('BKASH_CHECKOUT_URL_APP_SECRET'));
        $body_data_json = json_encode($body_data);

        $response = $this->curlWithBody('/checkout/token/grant', $header, 'POST', $body_data_json);

        $token = json_decode($response, true);

        return $token['id_token'];
    }

    public function create($request, $data)
    {
        $order_no = $data['order_no'];
        $amount = $data['amount'];
        $user_id = $data['user_id'];
        $header = $this->authHeaders($request);
        $body_data = array(
            'mode' => '0011',
            'payerReference' => $user_id,
            'callbackURL' => env('APP_FRONTED_URL') . '/api/bkash/execute',
            'amount' => $amount,
            'currency' => "BDT",
            'intent' => 'sale',
            'merchantInvoiceNumber' => $order_no
        );
        $body_data_json = json_encode($body_data);

        $response = $this->curlWithBody('/checkout/create', $header, 'POST', $body_data_json);
        return json_decode($response);
    }

    public function callback(Request $request)
    {

        $allRequest = $request->all();
        if (isset($allRequest['status']) && $allRequest['status'] == 'failure') {
            echo 'Payment Failure';
            return redirect(env("FRONTEND") . '/createApp');
        } else if (isset($allRequest['status']) && $allRequest['status'] == 'cancel') {
            echo 'Payment Cancell';
            return redirect(env("FRONTEND") . '/createApp');
        } else {

            $response = $this->execute($request, $allRequest['paymentID']);

            $arr = json_decode($response, true);


            if (array_key_exists("statusCode", $arr) && $arr['statusCode'] != '0000') {
                return $arr['statusMessage'];
            } else if (array_key_exists("message", $arr)) {
                // if execute api failed to response
                sleep(1);
                $query = $this->queryURL($request, $allRequest['paymentID']);
                return $query;
            }


            $this->bKashSuccess($request, $response);

            return redirect(env("APP_FRONTED_URL") . '/api/bkash/execute');
        }
    }

    public function execute(Request $request)
    {

        $header = $this->authHeaders($request);
        $paymentID = $request->paymentID;
        $status = $request->status;
        if ($status != 'success') {
            return 'Payment Failed';
        }
        $body_data = array(
            'paymentID' => $paymentID
        );
        $body_data_json = json_encode($body_data);

        $response = $this->curlWithBody('/checkout/execute', $header, 'POST', $body_data_json);

        $requestData = json_decode($response, true);
        $orderNo    = $requestData['merchantInvoiceNumber'] ?? '';
        $txnId      = $requestData['trxID'] ?? '';
        $userID     = $requestData['payerReference'] ?? '';
        $grandTotal = (float) ($requestData['amount'] ?? 0);
        $paymentId  = $requestData['paymentID'] ?? '';

        Transaction::create([
            'order_no'       => $orderNo,
            'transaction_id' => $txnId,
            'user_id'        => $userID,
            'total_amount'   => $grandTotal,
            'debit'          => $grandTotal,
            'credit'         => 0,
            'payment_method' => 'bKash',
            'payment_id'     => $paymentId,
            'description'    => $response
        ]);

        // Order Tracking
        OrderTracking::create([
            'order_no' => $orderNo,
            'status_id' => 2,
        ]);

        Order::where('order_no', $orderNo)->update(['status' => 2, 'payment_status' => 'Paid']);

        return $requestData;
    }

    public function queryURL(Request $request, $paymentID)
    {

        $header = $this->authHeaders($request);

        $body_data = array(
            'paymentID' => $paymentID,
        );
        $body_data_json = json_encode($body_data);

        $response = $this->curlWithBody('/tokenized/checkout/payment/status', $header, 'POST', $body_data_json);

        return $response;
    }

    public function bKashSuccess(Request $request, $success)
    {
        $requestData = json_decode($success);
        return $requestData;
    }


    public function bKashRefund(Request $request)
    {

        $token = $this->grant();
        Session::put('bkash_token', $token);
    }


    public function bKashRefundSend(Request $request)
    {
        $header = $this->authHeaders($request);

        $body_data = array(
            'paymentID' => $request->paymentID,
            'amount' => $request->amount,
            'trxID' => $request->trxID,
            'sku' => 'sku',
            'reason' => 'Quality issue'
        );

        $body_data_json = json_encode($body_data);

        $response = $this->curlWithBody('/checkout/payment/refund', $header, 'POST', $body_data_json);
        return json_decode($response);
    }
}
