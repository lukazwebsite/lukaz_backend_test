<?php

namespace App\Http\Controllers\Api;
use App\Models\Api\Notice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class NoticeController extends Controller
{

    public function index()
    {
        $notices = Notice::select('id', 'title', 'description', 'href', 'button_text')
                 ->orderBy('id', 'desc')
                 ->get();

        if ($notices->isEmpty()) {
            return response()->json([
                'message' => 'No notices found',
                'data'    => []
            ], 200);
        }

        return response()->json([
            'message' => 'Notices retrieved successfully',
            'data'    => $notices
        ], 200);
    }
}
