<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\Admin\Banner;
use Illuminate\Http\Request;
use App\Models\Admin\AccessUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Setup\Permission;
use Intervention\Image\Laravel\Facades\Image;

class BannerController extends Controller
{


    private $menuId = 28;
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

        $query = Banner::query()->orderBy('sequence');

        $append = [];

        if ($request->has('title') && $request->title !== null) {
            $query->where('title', 'like', '%' . $request->title . '%');
            $append['title']  = $request->title;
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

        $banners = $query->latest()->paginate(10)->appends($append)->withPath('/banner/paginate/filters');

        return Inertia::render('banner/Index', [
            'banners' => $banners,
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
        return Inertia::render('banner/Create');
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
            'title' => 'required|string'
        ]);

        $data['title'] = $request->title;
        $data['sequence'] = $request->sequence;
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;

        if ($request->hasFile('image')) {
            if (!File::exists(public_path('/banners'))) {
                File::makeDirectory(public_path('/banners'), 0755, true);
            }

            $upload = $request->file('image');
            $image = Image::read($upload);

            $name = Str::random() . '.' . $upload->getClientOriginalExtension();
            $iconName =  $name;
            $data['image'] = $iconName;
            $image->save(public_path('/banners') . '/' . $name);
        }

        Banner::create($data);

        return redirect()->route('banner.index')->with('success', 'Banners created successfully.');
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

        $banner = Banner::where('id', $id)->first();

        return Inertia::render('banner/Edit', [
            'banner' => $banner
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

        $oldData = Banner::where('id', $id)->first();



        $request->validate([
            'title' => 'required|string',
        ]);


        $data['title'] = $request->title;
        $data['sequence'] = $request->sequence;
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['updated_by'] = Auth::user()->id;


        if ($request->hasFile('image')) {

            $request->validate([
                'image' => 'nullable|file|mimes:jpg,png,webp'
            ]);

            $filePath = public_path('banners/' . $oldData->image);
            if (!empty($oldData->image) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path('/banners'))) {
                File::makeDirectory(public_path('/banners'), 0755, true);
            }

            $upload = $request->file('image');
            $image = Image::read($upload);

            $name = Str::random() . '.' . $upload->getClientOriginalExtension();
            $iconName =  $name;
            $data['image'] = $iconName;
            $image->save(public_path('/banners') . '/' . $name);
        }

        Banner::where('id', $id)->update($data);

        return redirect()->route('banner.index')->with('success', 'Banner updated successfully.');
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
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return redirect()->route('banner.index')->with('success', 'Banner updated successfully.');
    }
}
