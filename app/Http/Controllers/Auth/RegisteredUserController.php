<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country_id' => $request->country_id ?? 18,
            'role_id' => $request->role_id ?? 5,
            'mobile' => $request->mobile ?? '',
            'status' => $request->status ?? 1,
            'otp' => rand(100000, 99999),
            'description' => $request->description ?? '',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }


    /**
     * Otp update password function goes here
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function otpUpdate(Request $request) {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);


        if(Session::get('otp') != $request->otp){
            return response()->json('The OTP you entered seems to be incorrect. Please check and try again. 0');
        }

        $user = User::where('otp', Session::get('otp'))->where('mobile', Session::get('mobile'))->first();


        if(!empty($user)){

            $update['otp'] =  rand(100000, 99999);
            $update['password'] =  Hash::make($request->password);
            User::where('otp', Session::get('otp'))->where('mobile', Session::get('mobile'))->update($update);

            event(new Registered($user));

            Auth::login($user);

            return response()->json('success');
        }

        return response()->json('The OTP you entered seems to be incorrect. Please check and try again.');


    }

}
