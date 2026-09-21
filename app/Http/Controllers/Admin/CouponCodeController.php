<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Brand;
use App\Models\Api\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CouponCodeController extends Controller
{
     private $menuId = 24;
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

        $query = Coupon::query();

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
            $query->orWhere('discount', 'like', '%' . $request->name . '%');
            $query->orWhere('limit', 'like', '%' . $request->name . '%');
            $query->orWhere('max_discount', 'like', '%' . $request->name . '%');
            $append['name']  = $request->name;
        }

        if ($request->has('discount_type') && $request->discount_type !== null) {
            $query->where('discount_type', $request->discount_type);
            $append['discount_type']  = $request->discount_type;
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status );
            $append['status']  = $request->status;
        }

        $coupons = $query->latest()->paginate(10)->appends($append)->withPath('/coupon/paginate/filters');

        return Inertia::render('coupons/index', [
            'coupons' => $coupons,
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
        return Inertia::render('coupons/create');
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
            'name' => 'required|string|max:255|unique:coupons,name',
            'start_date' => 'required|string|max:255',
            'end_date' => 'required|string|max:255',
            'discount' => 'required|string|max:255',
            'discount_type' => 'required|string|max:255',
            'limit' => 'required|string|max:255',
            'max_discount' => 'required|string|max:255',
        ]);




        $data['name'] = $request->name;
        $data['slug'] = Str::slug($request->name);
        $data['start_date'] = date("Y-m-d H:i", strtotime($request->start_date));
        $data['end_date'] = date("Y-m-d H:i", strtotime($request->end_date));
        $data['discount'] = $request->discount;
        $data['discount_type'] = $request->discount_type;
        $data['max_discount'] = (int)$request->max_discount;
        $data['limit'] = $request->limit;
        $data['sequance'] = $request->sequance;
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;



        if($request->hasFile('banner')){
            if (!File::exists(public_path('/coupons/banner'))) {
                File::makeDirectory(public_path('/coupons/banner'), 0755, true);
            }

            $upload = $request->file('banner');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'banner/'.$name;
            $data['banner'] = $iconName;
            $image->save(public_path('/coupons/banner').'/'.$name);

        }

        if($request->hasFile('thumbnail')){
            if (!File::exists(public_path('/coupons/thumbnail'))) {
                File::makeDirectory(public_path('/coupons/thumbnail'), 0755, true);
            }

            $upload = $request->file('thumbnail');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'thumbnail/'.$name;
            $data['thumbnail'] = $iconName;
            $image->save(public_path('/coupons/thumbnail').'/'.$name);

        }

        if($request->hasFile('icon')){
            if (!File::exists(public_path('/coupons/icon'))) {
                File::makeDirectory(public_path('/coupons/icon'), 0755, true);
            }

            $upload = $request->file('icon');
            $image = Image::read($upload);

            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();
            $iconName =  'icon/'.$name;
            $data['icon'] = $iconName;
            $image->save(public_path('/coupons/icon').'/'.$name);

        }

        Coupon::create($data);

        return redirect()->route('coupon.index')->with('success', 'Coupons created successfully.');
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


        $coupon = Coupon::where('id', $id)->first();

        return Inertia::render('coupons/edit', [
            'coupon' => $coupon
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

        $oldData = Coupon::where('id',$id)->first();



        $request->validate([
            'name' => 'required|string|max:255|unique:coupons,name,'.$id,
            'start_date' => 'required|string|max:255',
            'end_date' => 'required|string|max:255',
            'discount' => 'required|string|max:255',
            'discount_type' => 'required|string|max:255',
            'limit' => 'required|string|max:255',
            'max_discount' => 'required|string|max:255',
        ]);


        $data['name'] = $request->name;
        $data['slug'] = Str::slug($request->name);
        $data['start_date'] = date("Y-m-d H:i", strtotime($request->start_date));
        $data['end_date'] = date("Y-m-d H:i", strtotime($request->end_date));
        $data['discount'] = $request->discount;
        $data['discount_type'] = $request->discount_type;
        $data['max_discount'] = (int)$request->max_discount;
        $data['limit'] = $request->limit;
        $data['sequance'] = $request->sequance;
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['updated_by'] = Auth::user()->id;





        if($request->hasFile('banner')){

            $request->validate([
                'banner' => 'nullable|file|mimes:jpg,png,webp',
            ]);

            $filePath = public_path('coupons/'.$oldData->banner);
            if (!empty($oldData->banner) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path('/coupons/banner'))) {
                File::makeDirectory(public_path('/coupons/banner'), 0755, true);
            }

            $upload = $request->file('banner');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'banner/'.$name;
            $data['banner'] = $iconName;
            $image->save(public_path('/coupons/banner').'/'.$name);

        }

        if($request->hasFile('thumbnail')){

            $request->validate([
                'thumbnail' => 'nullable|file|mimes:jpg,png,webp',
            ]);

            $filePath = public_path('coupons/'.$oldData->thumbnail);
            if (!empty($oldData->thumbnail) && file_exists($filePath)) {
                unlink($filePath);
            }

            if (!File::exists(public_path('/coupons/thumbnail'))) {
                File::makeDirectory(public_path('/coupons/thumbnail'), 0755, true);
            }

            $upload = $request->file('thumbnail');
            $image = Image::read($upload);
            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'thumbnail/'.$name;
            $data['thumbnail'] = $iconName;
            $image->save(public_path('/coupons/thumbnail').'/'.$name);

        }

        if($request->hasFile('icon')){

            $request->validate([
                'icon' => 'nullable|file|mimes:jpg,png,webp'
            ]);

            $filePath = public_path('coupons/'.$oldData->icon);
            if (!empty($oldData->icon) && file_exists($filePath)) {
                unlink($filePath);

            }


            if (!File::exists(public_path('/coupons/icon'))) {
                File::makeDirectory(public_path('/coupons/icon'), 0755, true);
            }

            $upload = $request->file('icon');
            $image = Image::read($upload);

            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();

            $iconName =  'icon/'.$name;
            $data['icon'] = $iconName;
            $image->save(public_path('/coupons/icon').'/'.$name);




        }

        Coupon::where('id', $id)->update($data);

        return redirect()->route('coupon.index')->with('success', 'Coupon updated successfully.');
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
        $brand = Coupon::findOrFail($id);
        $brand->delete();

        return redirect()->route('coupon.index')->with('success', 'Brand deleted successfully.');
    }
}
