<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Admin\Brand;
use Illuminate\Support\Str;
use App\Models\Admin\Notice;
use Illuminate\Http\Request;
use App\Models\Admin\AccessUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Setup\Permission;
use App\Models\Api\Notice as ApiNotice;
use Intervention\Image\Laravel\Facades\Image;
use PHPUnit\Framework\TestStatus\Notice as TestStatusNotice;

class NoticeController extends Controller
{

    private $menuId = 23;
    private $uploadPath = "category";
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

        $query = Notice::query();

        $append = [];



        if (($request->has('fromDate') && $request->fromDate !== null) && $request->toDate == null) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime(Carbon::now()))]);
            $append['fromDate']  = $request->fromDate;
        }

        if (($request->has('fromDate') && $request->fromDate !== null) && ($request->has('toDate') && $request->toDate !== null)) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime($request->toDate))]);
            $append['toDate']  = $request->toDate;
            $append['fromDate']  = $request->fromDate;
        }

        if ($request->has('title') && $request->title !== null) {
            $query->where('title', 'like', '%' . $request->title . '%');
            $append['title']  = $request->title;
        }

        if ($request->has('description') && $request->description !== null) {
            $query->where('description', 'like', '%' . $request->description . '%');
            $append['description']  = $request->description;
        }


        if ($request->has('buttonText') && $request->buttonText !== null) {
            $query->where('button_text', 'like', '%' . $request->buttonText . '%');
            $append['buttonText']  = $request->buttonText;
        }

        if ($request->has('href') && $request->href !== null) {
            $query->where('href', 'like', '%' . $request->href . '%');
            $append['href']  = $request->href;
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
            $append['status']  = $request->status;
        }



        $notices = $query->latest()->paginate(10)->appends($append)->withPath('/notices/paginate/filters');

        return Inertia::render('notice/index', [
            'notices' => $notices,
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

        return Inertia::render('notice/create');
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
            'title' => 'required|string|max:255|unique:notices,title',
            'description' => 'nullable|string',
            'buttonText' => 'nullable|string|max:100',
            'href' => 'nullable|url|max:255',
        ]);

        $data['title'] = $request->title;
        $data['description'] = $request->description;
        $data['button_text'] = $request->buttonText;
        $data['href'] = $request->href;
        $data['created_by'] = Auth::user()->id;


        Notice::create($data);

        return redirect()->route('notice.index')->with('success', 'Notice created successfully.');
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
        $notice = Notice::findOrFail($id);

        return Inertia::render('notice/edit', [
            'notice' => $notice
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


        $request->validate([
            'title' => 'required|string|max:255|unique:notices,title,' . $id,
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'href' => 'nullable|url|max:255',
            'status' => 'required|in:0,1',
        ]);

        $data['title'] = $request->title;
        $data['description'] = $request->description;
        $data['button_text'] = $request->button_text;
        $data['href'] = $request->href;
        $data['status'] = $request->status;
        $data['updated_by'] = Auth::user()->id;

        Notice::where('id', $id)->update($data);

        return redirect()->route('notice.index')->with('success', 'Notice updated successfully.');
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
        $notice = Notice::findOrFail($id);
        $notice->delete();

        return redirect()->route('notice.index')->with('success', 'Notice deleted successfully.');
    }
}
