<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SMSCongroller extends Controller
{

    private $smsText = "Your OTP for password reset is [otp]. It will expire in 5 minutes. Do not share this code with anyone.";


    function sms_send($number, $msg) {
        $url = "http://bulksmsbd.net/api/smsapi";
        $api_key = "LkUvakvlDjl03QzVE5zd";
        $senderid = "8809648906084";
        $number = "88".$number;
        $message = $msg;

        $data = [
            "api_key" => $api_key,
            "senderid" => $senderid,
            "number" => $number,
            "message" => $message
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }



    public function forgetPassword($msg, $otp, $number){


        Session::put('otp', $otp);
        Session::put('mobile', $number);

        $this->sms_send($number, $msg);


    }

}
