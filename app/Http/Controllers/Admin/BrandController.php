<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Brand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    private $menuId = 8;
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

        $query = Brand::query();

        $append = [];

        if (($request->has('fromDate') && $request->fromDate !== null ) && $request->toDate == null) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime(Carbon::now()))]);
            $append['fromDate']  = $request->fromDate;
        }

        if (($request->has('fromDate') && $request->fromDate !== null) && ($request->has('toDate') && $request->toDate !== null)) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime($request->toDate))]);
            $append['toDate']  = $request->toDate;
            $append['fromDate']  = $request->fromDate;
        }

        if ($request->has('name') && $request->name !== null) {
            $query->where('name', 'like', '%' . $request->name . '%');
            $append['name']  = $request->name;
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status );
            $append['status']  = $request->status;
        }

        $brands = $query->latest()->paginate(10)->appends($append)->withPath('/brands/paginate/filters');

        return Inertia::render('brand/index', [
            'brands' => $brands,
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
        return Inertia::render('brand/Create');
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

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'icon' => 'required|file|mimes:jpg,png,webp|max:5120',
            'thumbnail' => 'required|file|mimes:jpg,png,webp|max:5120',
        ]);



        $data['name'] = $request->name;
        $data['description'] = $request->description;
        $data['slug'] = Str::slug($request->name);
        $data['status'] = $request->status;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;


        if($request->hasFile('banner')){
            if (!File::exists(public_path('/brand/banner'))) {
                File::makeDirectory(public_path('/brand/banner'), 0755, true);
            }

            $upload = $request->file('banner');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'banner/'.$name;
            $data['banner'] = $iconName;
            $image->save(public_path('/brand/banner').'/'.$name);

        }

        if($request->hasFile('thumbnail')){
            if (!File::exists(public_path('/brand/thumbnail'))) {
                File::makeDirectory(public_path('/brand/thumbnail'), 0755, true);
            }

            $upload = $request->file('thumbnail');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'thumbnail/'.$name;
            $data['thumbnail'] = $iconName;
            $image->save(public_path('/brand/thumbnail').'/'.$name);

        }

        if($request->hasFile('icon')){
            if (!File::exists(public_path('/brand/icon'))) {
                File::makeDirectory(public_path('/brand/icon'), 0755, true);
            }

            $upload = $request->file('icon');
            $image = Image::read($upload);

            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'icon/'.$name;
            $data['icon'] = $iconName;
            $image->save(public_path('/brand/icon').'/'.$name);


        }

        Brand::create($data);

        return redirect()->route('brand.index')->with('success', 'Brand created successfully.');
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
        $brand = Brand::findOrFail($id);

        return Inertia::render('brand/Edit', [
            'brand' => $brand
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

        $oldData = Brand::where('id',$id)->first();



        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,'.$id,
        ]);


        $data['name'] = $request->name;
        $data['description'] = $request->description;
        $data['slug'] = Str::slug($request->name);
        $data['status'] = $request->status;
        $data['updated_by'] = Auth::user()->id;





        if($request->hasFile('banner')){

            $request->validate([
                'banner' => 'nullable|file|mimes:jpg,png,webp|max:5120',
            ]);

            $filePath = public_path('brand/banner/'.$oldData->banner);
            if (!empty($oldData->banner) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path('/brand/banner'))) {
                File::makeDirectory(public_path('/brand/banner'), 0755, true);
            }

            $upload = $request->file('banner');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'banner/'.$name;
            $data['banner'] = $iconName;
            $image->save(public_path('/brand/banner').'/'.$name);

        }

        if($request->hasFile('thumbnail')){

            $request->validate([
                'thumbnail' => 'nullable|file|mimes:jpg,png,webp|max:5120',
            ]);

            $filePath = public_path('brand/thumbnail/'.$oldData->thumbnail);
            if (!empty($oldData->thumbnail) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path('/brand/thumbnail'))) {
                File::makeDirectory(public_path('/brand/thumbnail'), 0755, true);
            }

            $upload = $request->file('thumbnail');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'thumbnail/'.$name;
            $data['thumbnail'] = $iconName;
            $image->save(public_path('/brand/thumbnail').'/'.$name);

        }

        if($request->hasFile('icon')){

            $request->validate([
                'icon' => 'nullable|file|mimes:jpg,png,webp|max:5120'
            ]);

            $filePath = public_path('brand/icon/'.$oldData->icon);
            if (!empty($oldData->icon) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path('/brand/icon'))) {
                File::makeDirectory(public_path('/brand/icon'), 0755, true);
            }

            $upload = $request->file('icon');
            $image = Image::read($upload);

            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'icon/'.$name;
            $data['icon'] = $iconName;
            $image->save(public_path('/brand/icon').'/'.$name);




        }

        Brand::where('id', $id)->update($data);

        return redirect()->route('brand.index')->with('success', 'Brand updated successfully.');
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
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return redirect()->route('brand.index')->with('success', 'Brand deleted successfully.');
    }
}
