<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\Admin\TeamMember;
use Illuminate\Http\Request;
use App\Models\Admin\AccessUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Setup\Permission;
use Intervention\Image\Laravel\Facades\Image;

class TeamMemberController extends Controller
{

    private $menuId = 43;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId, 1);

        if (!$checkPermission) {
            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $menuAccess = AccessUser::where('user_id', Auth::user()->id)->where('menu_id', $this->menuId)->get([
            'user_id',
            'menu_id',
            'action_id'
        ]);

        $query = TeamMember::query();

        $append = [];

        if ($request->has('name') && $request->name !== null) {
            $query->where('name', 'like', '%' . $request->name . '%');
            $append['name']  = $request->name;
        }

        if ($request->has('designation') && $request->designation !== null) {
            $query->where('designation', 'like', '%' . $request->designation . '%');
            $append['designation']  = $request->designation;
        }

        if (($request->has('fromDate') && $request->fromDate !== null) && $request->toDate == null) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime(Carbon::now()))]);
            $append['fromDate']  = $request->fromDate;
        }

        if (($request->has('fromDate') && $request->fromDate !== null) && ($request->has('toDate') && $request->toDate !== null)) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime($request->toDate))]);
            $append['toDate']  = $request->toDate;
            $append['fromDate']  = $request->fromDate;
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
            $append['status']  = $request->status;
        }

        // Same order the storefront uses, so the admin list matches what customers see.
        $teamMembers = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->appends($append)
            ->withPath('/team_member/paginate/filters');

        return Inertia::render('teammember/Index', [
            'teamMembers' => $teamMembers,
            'menuAccess' => $menuAccess,
            'checkPermission' => $checkPermission
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId, 2);

        if (!$checkPermission) {
            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        // Next gap-of-ten slot, so a new member lands at the end without renumbering.
        $nextSortOrder = (int) TeamMember::max('sort_order') + 10;

        return Inertia::render('teammember/Create', [
            'nextSortOrder' => $nextSortOrder,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId, 2);

        if (!$checkPermission) {
            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $request->validate([
            'name' => 'required|string',
            'designation' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp'
        ]);

        $data['name'] = $request->name;
        $data['designation'] = $request->designation;
        $data['whatsapp'] = $this->cleanWhatsapp($request->whatsapp);
        $data['sort_order'] = $request->sort_order ?? 0;
        $data['status'] = $request->status;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;

        if ($request->hasFile('image')) {
            if (!File::exists(public_path('/team'))) {
                File::makeDirectory(public_path('/team'), 0755, true);
            }

            $upload = $request->file('image');
            $image = Image::read($upload);

            $name = Str::random() . '.' . $upload->getClientOriginalExtension();
            $data['image'] = $name;
            $image->save(public_path('/team') . '/' . $name);
        }

        TeamMember::create($data);

        return redirect()->route('team.member.index')->with('success', 'Team Member created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 1);

        if (!$checkPermission) {
            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 3);

        if (!$checkPermission) {
            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $teamMember = TeamMember::where('id', $id)->first();

        return Inertia::render('teammember/Edit', [
            'teamMember' => $teamMember,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 3);

        if (!$checkPermission) {
            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $oldData = TeamMember::where('id', $id)->first();

        $request->validate([
            'name' => 'required|string',
            'designation' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['name'] = $request->name;
        $data['designation'] = $request->designation;
        $data['whatsapp'] = $this->cleanWhatsapp($request->whatsapp);
        $data['sort_order'] = $request->sort_order ?? 0;
        $data['status'] = $request->status;
        $data['updated_by'] = Auth::user()->id;

        if ($request->hasFile('image')) {

            $request->validate([
                'image' => 'nullable|file|mimes:jpg,jpeg,png,webp'
            ]);

            $filePath = public_path('team/' . $oldData->image);
            if (!empty($oldData->image) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path('/team'))) {
                File::makeDirectory(public_path('/team'), 0755, true);
            }

            $upload = $request->file('image');
            $image = Image::read($upload);

            $name = Str::random() . '.' . $upload->getClientOriginalExtension();
            $data['image'] = $name;
            $image->save(public_path('/team') . '/' . $name);
        }

        TeamMember::where('id', $id)->update($data);

        return redirect()->route('team.member.index')->with('success', 'Team Member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 4);

        if (!$checkPermission) {
            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $teamMember = TeamMember::findOrFail($id);

        $filePath = public_path('team/' . $teamMember->image);
        if (!empty($teamMember->image) && file_exists($filePath)) {
            unlink($filePath);
        }

        $teamMember->delete();

        return redirect()->route('team.member.index')->with('success', 'Team Member deleted successfully.');
    }

    /**
     * wa.me accepts digits only, so strip +, spaces and dashes on the way in.
     */
    private function cleanWhatsapp($whatsapp)
    {
        if (empty($whatsapp)) {
            return null;
        }

        return preg_replace('/\D/', '', $whatsapp);
    }
}
