<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Api\Subscription;
use App\Http\Controllers\Controller;

class SubscriptionController extends Controller
{
    public function store(Request $request)
    {
        try {

            $email = $request->input('email');
            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is required.'
                ], 400);
            }

            $subscription = Subscription::create([
                'email' => $email,
                'status' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscribed successfully!',
                'data' => $subscription
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while subscribing.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
