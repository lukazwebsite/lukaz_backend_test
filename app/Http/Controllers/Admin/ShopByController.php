<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\Admin\ShopBy;
use App\Models\Admin\Category;
use Illuminate\Http\Request;
use App\Models\Admin\AccessUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Setup\Permission;
use Intervention\Image\Laravel\Facades\Image;

class ShopByController extends Controller
{


    private $menuId = 34;
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

        $query = ShopBy::query();

        $append = [];

        if ($request->has('name') && $request->name !== null) {

            $query->where('name', 'like', '%' . $request->name . '%');
            $append['name']  = $request->name;
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

        $shopbys = $query->latest()->paginate(10)->appends($append)->withPath('/shop_by/paginate/filters');


        return Inertia::render('shopby/Index', [
            'shopbys' => $shopbys,
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

        $categories = Category::get()->sortBy('name')->values()->toArray();

        return Inertia::render('shopby/Create', [
            'categories' => $categories,
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
            'name' => 'required|string'
        ]);

        $data['name'] = $request->name;
        $data['url'] = $request->url;
        $data['categories'] = json_decode($request->categories, true);
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;

        if ($request->hasFile('thumbnail')) {
            if (!File::exists(public_path('/shopby'))) {
                File::makeDirectory(public_path('/shopby'), 0755, true);
            }

            $upload = $request->file('thumbnail');
            $thumbnail = Image::read($upload);

            $name = Str::random() . '.' . $upload->getClientOriginalExtension();
            $iconName =  $name;
            $data['thumbnail'] = $iconName;
            $thumbnail->save(public_path('/shopby') . '/' . $name);
        }

        ShopBy::create($data);

        return redirect()->route('shop.by.index')->with('success', 'Shop By created successfully.');
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

        $shopby = ShopBy::where('id', $id)->first();

        $categories = Category::get()->sortBy('name')->values()->toArray();

        return Inertia::render('shopby/Edit', [
            'shopby' => $shopby,
            'categories' => $categories
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

        $oldData = ShopBy::where('id', $id)->first();


        $request->validate([
            'name' => 'required|string',
        ]);

        $data['name'] = $request->name;
        $data['url'] = isset($request->url) ? $request->url : null;
        if ($request->categories) {
            $data['categories'] = json_decode($request->categories, true);
        }
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['updated_by'] = Auth::user()->id;


        if ($request->hasFile('thumbnail')) {

            $request->validate([
                'thumbnail' => 'nullable|file|mimes:jpg,png,webp'
            ]);

            $filePath = public_path('shopby/' . $oldData->thumbnail);
            if (!empty($oldData->thumbnail) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path('/shopby'))) {
                File::makeDirectory(public_path('/shopby'), 0755, true);
            }

            $upload = $request->file('thumbnail');
            $image = Image::read($upload);

            $name = Str::random() . '.' . $upload->getClientOriginalExtension();
            $iconName =  $name;
            $data['thumbnail'] = $iconName;
            $image->save(public_path('/shopby') . '/' . $name);
        }


        ShopBy::where('id', $id)->update($data);

        return redirect()->route('shop.by.index')->with('success', 'Shop By updated successfully.');
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
        $shopby = ShopBy::findOrFail($id);
        $shopby->delete();

        return redirect()->route('shop.by.index')->with('success', 'Shop By updated successfully.');
    }
}
