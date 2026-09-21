<?php

namespace App\Http\Controllers\Api;

use App\Models\Api\Contract;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContractController extends Controller
{
    public function store(Request $request)
    {
        try {

            $contract = Contract::create([
                'name' =>  $request->input('name'),
                'email' =>  $request->input('email'),
                'phone' => $request->input('phone'),
                'message' => $request->input('message'),
                'status' => 1,
            ]);

            return response()->json([
                'message' => 'Contract submitted successfully.',
                'data' => $contract
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
