<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    private $menuId = 9;
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


        $query = Category::with('childrenRecursive')
        ->when(!($request->filled('name') || $request->filled('fromDate') || $request->filled('toDate') || $request->filled('status')), function ($q) {
            $q->whereNull('parent_id');
        });

        $append = [];

        if ($request->has('name') && $request->name !== null) {
            $query->where('name', 'like', '%' . $request->name . '%');
            $append['name']  = $request->name;
        }

        if (($request->has('fromDate') && $request->fromDate !== null ) && $request->toDate == null) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime(Carbon::now()))]);
            $append['fromDate']  = $request->fromDate;
        }

        if (($request->has('fromDate') && $request->fromDate !== null) && ($request->has('toDate') && $request->toDate !== null)) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime($request->toDate))]);
            $append['toDate']  = $request->toDate;
            $append['fromDate']  = $request->fromDate;
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status );
            $append['status']  = $request->status;
        }

        $categories = $query->latest()->paginate(10)->appends($append)->withPath('/categories/paginate/filters');



        return Inertia::render('category/index', [
            'categories' => $categories,
            'menuAccess' => $menuAccess,
            'checkPermission' => $checkPermission
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId, 1);

        if (!$checkPermission) {

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }
        $parents = Category::get(['id', 'name']);

        return Inertia::render('category/Create', [
            'parents' => $parents
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
            'name' => 'required|string|max:255|unique:categories,name',
            'icon' => 'required|file|mimes:jpg,png,webp|max:5120',
            'thumbnail' => 'required|file|mimes:jpg,png,webp|max:5120',
            'featured' => 'nullable|in:0,1',
            'menuImage' => 'nullable|file|mimes:jpg,png,webp|max:5120',
        ]);



        $data['name'] = $request->name;
        $data['parent_id'] = $request->parent_id;
        $data['description'] = $request->description;
        $data['slug'] = Str::slug($request->name);
        $data['status'] = $request->status;
        $data['isActive'] = $request->isActive;
        $data['featured'] = $request->featured ?? 0;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;


        if($request->hasFile('banner')){
            if (!File::exists(public_path($this->uploadPath.'/banner'))) {
                File::makeDirectory(public_path($this->uploadPath.'/banner'), 0755, true);
            }

            $upload = $request->file('banner');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'banner/'.$name;
            $data['banner'] = $iconName;
            $image->save(public_path($this->uploadPath.'/banner').'/'.$name);

        }

        if($request->hasFile('thumbnail')){
            if (!File::exists(public_path($this->uploadPath.'/thumbnail'))) {
                File::makeDirectory(public_path($this->uploadPath.'/thumbnail'), 0755, true);
            }

            $upload = $request->file('thumbnail');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'thumbnail/'.$name;
            $data['thumbnail'] = $iconName;
            $image->save(public_path($this->uploadPath.'/thumbnail').'/'.$name);

        }

        if($request->hasFile('icon')){
            if (!File::exists(public_path($this->uploadPath.'/icon'))) {
                File::makeDirectory(public_path($this->uploadPath.'/icon'), 0755, true);
            }

            $upload = $request->file('icon');
            $image = Image::read($upload);

            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'icon/'.$name;
            $data['icon'] = $iconName;
            $image->save(public_path($this->uploadPath.'/icon').'/'.$name);


        }

        if($request->hasFile('menuImage')){
            if (!File::exists(public_path($this->uploadPath.'/menuImage'))) {
                File::makeDirectory(public_path($this->uploadPath.'/menuImage'), 0755, true);
            }

            $upload = $request->file('menuImage');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'menuImage/'.$name;
            $data['menuImage'] = $iconName;
            $image->save(public_path($this->uploadPath.'/menuImage').'/'.$name);

        }

        Category::create($data);

        return redirect()->route('category.index')->with('success', 'Category created successfully.');
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

        $categories = Category::get(['id', 'name']);
        $category = Category::with('parent:id,name')->where('id', $id)->first();


        return Inertia::render('category/Edit', [
            'category' => $category,
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

        $oldData = Category::where('id',$id)->first();

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$id,
            'featured' => 'nullable|in:0,1',
        ]);


        $data['name'] = $request->name;
        $data['slug'] = Str::slug($request->name);
        $data['parent_id'] = $request->parent_id;
        $data['description'] = $request->description;
        $data['status'] = $request->status;
        $data['isActive'] = $request->isActive;
        $data['featured'] = $request->featured ?? 0;
        $data['updated_by'] = Auth::user()->id;

        if($request->hasFile('banner')){

            $request->validate([
                'banner' => 'nullable|file|mimes:jpg,png,webp|max:5120',
            ]);

            $filePath = public_path($this->uploadPath.'/banner'.'/'.$oldData->banner);
            if (!empty($oldData->banner) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path($this->uploadPath.'/banner'))) {
                File::makeDirectory(public_path($this->uploadPath.'/banner'), 0755, true);
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

            $filePath = public_path($this->uploadPath.'/thumbnail'.'/'.$oldData->thumbnail);
            if (!empty($oldData->thumbnail) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path($this->uploadPath.'/thumbnail'))) {
                File::makeDirectory(public_path($this->uploadPath.'/thumbnail'), 0755, true);
            }

            $upload = $request->file('thumbnail');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'thumbnail/'.$name;
            $data['thumbnail'] = $iconName;
            $image->save(public_path($this->uploadPath.'/thumbnail').'/'.$name);

        }

        if($request->hasFile('icon')){

            $request->validate([
                'icon' => 'nullable|file|mimes:jpg,png,webp|max:5120'
            ]);

            $filePath = public_path($this->uploadPath.'/icon'.'/'.$oldData->icon);
            if (!empty($oldData->icon) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path($this->uploadPath.'/icon'))) {
                File::makeDirectory(public_path($this->uploadPath.'/icon'), 0755, true);
            }

            $upload = $request->file('icon');
            $image = Image::read($upload);

            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'icon/'.$name;
            $data['icon'] = $iconName;
            $image->save(public_path($this->uploadPath.'/icon').'/'.$name);

        }

        if($request->hasFile('menuImage')){

            $request->validate([
                'menuImage' => 'nullable|file|mimes:jpg,png,webp|max:5120'
            ]);

            $filePath = public_path($this->uploadPath.'/'.$oldData->menuImage);
            if (!empty($oldData->menuImage) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path($this->uploadPath.'/menuImage'))) {
                File::makeDirectory(public_path($this->uploadPath.'/menuImage'), 0755, true);
            }

            $upload = $request->file('menuImage');
            $image = Image::read($upload);

            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'menuImage/'.$name;
            $data['menuImage'] = $iconName;
            $image->save(public_path($this->uploadPath.'/menuImage').'/'.$name);

        }


        Category::where('id', $id)->update($data);

        return redirect()->route('category.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Toggle the featured flag of the specified resource.
     */
    public function toggleFeatured(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 3);

        if (!$checkPermission) {

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $request->validate([
            'featured' => 'required|in:0,1',
        ]);

        Category::where('id', $id)->update([
            'featured' => $request->featured,
            'updated_by' => Auth::user()->id,
        ]);

        return back()->with('success', 'Featured status updated successfully.');
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
        $brand = Category::findOrFail($id);
        $brand->delete();

        return redirect()->route('category.index')->with('success', 'Category deleted successfully.');
    }
}
