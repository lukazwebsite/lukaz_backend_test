<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SMSCongroller;
use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{

    private $smsText = "Your PIN code for password reset is [otp]. It will expire in 5 minutes. Do not share this code with anyone.";
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required',
        ]);

        // User input (can be email or mobile)
        $login = $request->email; // same input field

        $status =  __('A reset otp will be sent if the account exists.');

        $getUser = User::where('mobile', $request->email)->orWhere('email', $request->email)->first();

            if(!empty($getUser)){

                $otp = rand(10000, 99999);

                User::where('id', $getUser->id)->update(['otp' => $otp]);

                $msg = str_replace("[otp]", $otp, $this->smsText);

                // Detect whether it's an email or mobile
                if(filter_var($login, FILTER_VALIDATE_EMAIL)){

                    Password::sendResetLink(
                        $request->only('email')
                    );

                    return back()->with('status', __('A reset link will be sent if the account exists.'));

                }else{
                    $sms = new SMSCongroller();
                    $sms->forgetPassword($msg, $otp, $request->email);


                }

        }



        return Inertia::render('auth/PasswordUpdate',
            [
                'status' => $status
            ]
        );



    }
}
