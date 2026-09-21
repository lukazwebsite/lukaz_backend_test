<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\SMSCongroller;
use App\Mail\SendOTPForgetPassword;
use Illuminate\Support\Facades\Mail;
class UserController extends Controller
{

    private $smsText = "Your PIN code for password reset is [otp]. It will expire in 5 minutes. Do not share this code with anyone.";
    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'mobile' => 'required',
                'password' => 'required',
            ]);

            $user = User::where('mobile', $credentials['mobile'])->first();


            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'message' => 'Invalid mobile or password.',
                    'status' => 'failed',
                ], 401);
            }

            // Delete previous tokens if you want single-device login
            $user->tokens()->delete();

            $token = $user->createToken($request->mobile)->plainTextToken;

            return response()->json([
                'token' => $token,
                'data' => $user,
                'message' => 'Login successful',
                'status' => 'success',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during login: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Logout successful',
                'status' => 'success',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error logging out: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    public function register(Request $request)
    {

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:users,mobile',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ], [
            'mobile.unique' => 'The mobile number has already been taken.',
            'name.required' => 'The name field is required.',
            'mobile.required' => 'The mobile field is required.',
            'password.required' => 'The password field is required.',
            'confirm_password.required' => 'The confirm password field is required.',
            'confirm_password.same' => 'The confirm password must match the password.',
        ]);


        if ($validated->fails()) {
            return response([
                'message' => $validated->errors(),
                'status' => 'error',
            ], 422);
        }

        try {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email ?? $request->mobile . '@example.com',
                'mobile' => $request->mobile,
                'country_id' => $request->country_id ?? 18,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken($request->mobile)->plainTextToken;


            return response([
                'message' => 'Registration successful.',
                'status' => 'success',
                'token' => $token,
                'data' => $user,
            ], 201);
        } catch (\Exception $e) {

            return response([
                "message" => "Something went wrong, please try again later.",
                "status" => "error",
                "error" => $e->getMessage(),
            ], 403);
        }
    }


    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function forgetOtp(Request $request)
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

                Mail::to($request->email)->send(new SendOTPForgetPassword($getUser));

                return response()->json([
                    'status' => $status,
                    'email' => $request->email
                ]);

            }else{
                $sms = new SMSCongroller();
                $sms->forgetPassword($msg, $otp, $request->email);


            }

        }



        return response()->json([
            'status' => $status,
            'email' => $request->email
        ]);

    }

    /**
     * Otp update password function goes here
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function otpUpdate(Request $request) {


        $request->validate([
            'password' => ['required', 'confirmed'],
            'otp' => "required",
            'email' => "required"
        ]);


        $user = User::where('otp', $request->otp)->where('mobile', $request->email)->first();

        if(empty($user)){
            return response()->json('The OTP you entered seems to be incorrect. Please check and try again.');
        }


        if(!empty($user)){

            $update['otp'] =  rand(100000, 99999);
            $update['password'] =  Hash::make($request->password);
            User::where('otp', $request->otp)->where('mobile', $request->email)->update($update);

            $token = $user->createToken($request->email)->plainTextToken;

            return response([
                'message' => 'Password reset successful.',
                'status' => 'success',
                'token' => $token,
                'data' => $user,
            ], 201);
        }

        return response()->json('The OTP you entered seems to be incorrect. Please check and try again.');


    }
}
