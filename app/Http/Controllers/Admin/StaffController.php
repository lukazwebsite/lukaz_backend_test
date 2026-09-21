<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Setup\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Str;
use Inertia\Inertia;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Branch;
use App\Models\Admin\Role;
use App\Models\User;
use Carbon\Carbon;

class StaffController extends Controller
{
    private $menuId = 2;

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


        $query = User::with(['branch', 'role']);

        $append = [
            'name'  => $request->name,
            'branch_id'  => $request->branch_id,
            'role_id'  => $request->role_id,
            'fromDate' => $request->fromDate,
            'toDate' => $request->toDate,
            'status' => $request->status,
        ];

        if (($request->has('fromDate') && $request->fromDate !== null ) && $request->toDate == null) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime(Carbon::now()))]);

        }

        if (($request->has('fromDate') && $request->fromDate !== null) && ($request->has('toDate') && $request->toDate !== null)) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime($request->toDate))]);
        }

        if ($request->has('name') && $request->name !== null) {
            $query->where('name', 'like', '%' . $request->name . '%');
            $query->orWhere('mobile', 'like', '%' . $request->name . '%');
            $query->orWhere('email', 'like', '%' . $request->name . '%');
        }

        if ($request->has('role_id') && $request->role_id !== null) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->has('branch_id') && $request->branch_id !== null) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        $staffs = $query->where('role_id', '!=', 5)->latest()->paginate(10)->appends($append)->withPath('/staff/paginate/filters');

        $roles = Role::where('status', 1)->get(['id', 'name']);
        $branches = Branch::where('status', 1)->get(['id', 'name']);

        return Inertia::render('staff/index', [
            'staffs' => $staffs,
            'menuAccess' => $menuAccess,
            'roles' => $roles,
            'branches' => $branches,
            'append' => $append,
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

        $roles = Role::where('status', 1)->get(['id', 'name']);
        $branches = Branch::where('status', 1)->get(['id', 'name']);

        return Inertia::render('staff/create',
            [
                'roles' => $roles,
                "branches" => $branches
            ]
        );
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
            'role_id' => 'required|numeric',
            'branch_id' => 'required|numeric',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:users,mobile',
            'email' => 'required|string|max:255|unique:users,email',
            'password' => 'required|confirmed|min:6'
        ]);




        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['password'] = bcrypt($request->password);
        $data['mobile'] = $request->mobile;
        $data['role_id'] = $request->role_id;
        $data['branch_id'] = $request->branch_id;
        $data['role_id'] = $request->role_id;
        $data['joining'] = date("Y-m-d H:i", strtotime($request->joining));
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;


        User::create($data);

        return redirect()->route('staff.index')->with('success', 'Coupons created successfully.');
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


        $staff = User::where('id', $id)->first();

        $roles = Role::where('status', 1)->get(['id', 'name']);
        $branches = Branch::where('status', 1)->get(['id', 'name']);

        return Inertia::render('staff/edit',
            [
                'roles' => $roles,
                "branches" => $branches,
                'staff' => $staff
            ]
        );

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
            'role_id' => 'required|numeric',
            'branch_id' => 'required|numeric',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:users,mobile,'.$id,
            'email' => 'required|string|max:255|unique:users,email,'.$id,
        ]);


        if($request->password != ''){
            $request->validate([
                'password' => 'required|confirmed|min:6'
            ]);

             $data['password'] = bcrypt($request->password);
        }


        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['mobile'] = $request->mobile;
        $data['role_id'] = $request->role_id;
        $data['branch_id'] = $request->branch_id;
        $data['role_id'] = $request->role_id;
        $data['joining'] = date("Y-m-d H:i", strtotime($request->joining));
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;

        User::where('id', $id)->update($data);

        return redirect()->route('staff.index')->with('success', 'Staff updated successfully.');
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
        $brand = User::findOrFail($id);
        $brand->delete();

        return redirect()->route('staff.index')->with('success', 'Brand deleted successfully.');
    }
}
