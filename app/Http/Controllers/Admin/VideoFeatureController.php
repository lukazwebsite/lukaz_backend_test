<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Inertia\Inertia;

use Illuminate\Http\Request;
use App\Models\Admin\AccessUser;
use App\Models\Admin\VideoFeature;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Setup\Permission;

class VideoFeatureController extends Controller
{
    private $menuId = 25;
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

        $query = VideoFeature::query();

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

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
            $append['status']  = $request->status;
        }

        $videos  = $query->latest()->paginate(10)->appends($append)->withPath('/video/paginate/filters');

        return Inertia::render('video/index', [
            'videos' => $videos,
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
        return Inertia::render('video/create');
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
            'title' => 'required|string|max:255',
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'video_link' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
        ]);

        $data['title'] = $request->title;
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['video_link'] = $request->video_link;
        $data['button_text'] = $request->button_text;
        $data['button_link'] = $request->button_link;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;


        VideoFeature::create($data);

        return redirect()->route('video.index')->with('success', 'Video Feature created successfully.');
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


        $video = VideoFeature::where('id', $id)->first();

        return Inertia::render('video/edit', [
            'video' => $video
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
            'title' => 'required|string|max:255',
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'video_link' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:100',
        ]);

        $data['title'] = $request->title;
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['video_link'] = $request->video_link;
        $data['button_text'] = $request->button_text;
        $data['button_link'] = $request->button_link;
        $data['updated_by'] = Auth::user()->id;


        VideoFeature::where('id', $id)->update($data);

        return redirect()->route('video.index')->with('success', 'Video Feature updated successfully.');
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
        $video = VideoFeature::findOrFail($id);
        $video->delete();

        return redirect()->route('video.index')->with('success', 'Video Feature deleted successfully.');
    }
}
