<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin\TeamMember;

class TeamMemberController extends Controller
{
    /**
     * Active members only, already ordered, so the storefront does no sorting.
     */
    public function index()
    {
        $teamMembers = TeamMember::where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'designation' => $member->designation,
                    'image_url' => $member->image_url,
                    'whatsapp' => $member->whatsapp,
                    'sort_order' => $member->sort_order,
                ];
            });

        return response()->json($teamMembers, 200);
    }
}
