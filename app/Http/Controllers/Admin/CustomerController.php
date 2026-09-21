<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Setup\Permission;
use App\Http\Controllers\SMSCongroller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Str;
use Inertia\Inertia;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Branch;
use App\Models\Admin\Role;
use App\Models\User;
use Carbon\Carbon;

class CustomerController extends Controller
{
    private $menuId = 3;

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
            'fromDate' => $request->fromDate,
            'toDate' => $request->toDate,
            'status' => $request->status,
        ];

        if (($request->has('fromDate') && $request->fromDate !== null) && $request->toDate == null) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime(Carbon::now()))]);
        }

        if (($request->has('fromDate') && $request->fromDate !== null) && ($request->has('toDate') && $request->toDate !== null)) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime($request->toDate))]);
        }

        if ($request->has('name') && $request->name !== null) {
            $query->whereAny(['name', 'mobile'],  'like', '%' . $request->name . '%');
        }

        if ($request->has('branch_id') && $request->branch_id !== null) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        $customers = $query->where('role_id', 5)->orderBy('id', 'desc')->paginate(10)->appends($append)->withPath('/customer/paginate/filters');


        $branches = Branch::where('status', 1)->get(['id', 'name']);

        return Inertia::render('customer/Index', [
            'customers' => $customers,
            'menuAccess' => $menuAccess,
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

        $roles = Role::where('status', '!=', 2)->get(['id', 'name']);
        $branches = Branch::where('status', 1)->get(['id', 'name']);

        return Inertia::render(
            'customer/Create',
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

        if($request->password != null){
            $data['password'] = bcrypt($request->password);
            $request->validate([
                'password' => 'required|confirmed|min:6'
            ]);
        }else{

            $data['password'] = bcrypt("password");
        }

        if($request->email != null){
            $request->validate([
                'email' => 'required|string|max:255|unique:users,email',
            ]);
            $data['email'] = $request->email;
         }else{
            $data['email'] = $request->mobile;
        }

        $request->validate([
            'role_id' => 'required|numeric',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:users,mobile',
        ]);




        $data['name'] = $request->name;

        $data['mobile'] = $request->mobile;
        // $data['branch_id'] = $request->branch_id;
        $data['role_id'] = $request->role_id;
        $data['joining'] = $request->joining != null ? date("Y-m-d H:i", strtotime($request->joining)) : date("Y-m-d H:i", strtotime(now())) ;
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;


        $user = User::create($data);

        if($user){

            $sms = new SMSCongroller();
            $sms->sms_send($request->mobile, "Welcome to ".env('APP_NAME').", ".$request->name.". Your account has been successfully created. We're glad to have you with us.");

        }

        return redirect()->route('customer.index')->with('success', 'Customer created successfully.');
    }


    /**
     * Quick customer creation from the POS screen.
     *
     * Only the mobile number is required. Name, email and password stay empty
     * unless the cashier types them, so a walk-in customer can be registered
     * without leaving the sale in progress. The response is JSON because the
     * POS keeps the current cart and only refreshes its customer dropdown.
     */
    public function posStore(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId, 2);

        if (!$checkPermission) {
            return response()->json([
                'message' => 'You do not have permission to create a customer.',
            ], 403);
        }

        // Only the mobile is mandatory. Anything the cashier leaves blank is
        // stored as NULL rather than a placeholder value.
        $validated = $request->validate([
            'mobile' => 'required|string|max:15|unique:users,mobile',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:6|confirmed',
            'status' => 'nullable|in:0,1',
            'description' => 'nullable|string|max:1000',
        ]);

        $blankToNull = fn($value) => ($value === '' || $value === null) ? null : $value;

        $password = $blankToNull($validated['password'] ?? null);

        $customer = User::create([
            'name' => $blankToNull($validated['name'] ?? null),
            'mobile' => $validated['mobile'],
            'email' => $blankToNull($validated['email'] ?? null),
            // NULL password means this account simply cannot be logged into.
            'password' => $password === null ? null : bcrypt($password),
            'role_id' => 5,
            'status' => $validated['status'] ?? 1,
            'description' => $blankToNull($validated['description'] ?? null),
            'joining' => date("Y-m-d H:i"),
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ]);

        // A failing SMS gateway must not lose a customer that is already saved.
        try {
            $sms = new SMSCongroller();
            $sms->sms_send($customer->mobile, "Welcome to " . env('APP_NAME') . ", " . ($customer->name ?: 'Customer') . ". Your account has been successfully created. We're glad to have you with us.");
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'mobile' => $customer->mobile,
            ],
        ]);
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


        $customer = User::where('id', $id)->first();

        $roles = Role::where('id', 5)->where('status', 1)->get(['id', 'name']);
        $branches = Branch::where('status', 1)->get(['id', 'name']);

        return Inertia::render(
            'customer/Edit',
            [
                'roles' => $roles,
                "branches" => $branches,
                'customer' => $customer
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
            // 'branch_id' => 'required|numeric',
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15|unique:users,mobile,' . $id,
            'email' => 'required|string|max:255|unique:users,email,' . $id,
        ]);


        if ($request->password != '') {
            $request->validate([
                'password' => 'required|confirmed|min:6'
            ]);

            $data['password'] = bcrypt($request->password);
        }


        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['mobile'] = $request->mobile;
        // $data['branch_id'] = $request->branch_id;
        $data['role_id'] = $request->role_id;
        $data['joining'] = date("Y-m-d H:i", strtotime($request->joining));
        $data['status'] = $request->status;
        $data['description'] = $request->description;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;

        User::where('id', $id)->update($data);

        return redirect()->route('customer.index')->with('success', 'Customer updated successfully.');
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
        $Customer = User::findOrFail($id);
        $Customer->delete();

        return redirect()->route('customer.index')->with('success', 'Customer deleted successfully.');
    }
}
