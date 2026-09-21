<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Additional;
use App\Models\Admin\Brand;
use App\Models\Admin\Category;
use App\Models\Api\Country;
use App\Models\InternationalOrder;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InternationalOrderControler extends Controller
{
    private $menuId = 11;
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


        $append = [
            'name'     => $request->name,
            'status'     => $request->status,
            'fromDate' => $request->fromDate,
            'toDate'   => $request->toDate
        ];

         $internationals = InternationalOrder::with(['country', 'order_status'])->where(function($query) use($request) {

            $query->where(function($query) use($request){
                if ( $request->name != "" ) {
                    $query->where('item_name', "LIKE", "%".$request->name."%");
                    $query->orWhere('order_no', "LIKE", "%".$request->name."%");
                    $query->orWhere('whats_app_no', "LIKE", "%".$request->whats_app_no."%");
                    $query->orWhere('regular_price', "LIKE", "%".$request->name."%");
                    $query->orWhere('current_price', "LIKE", "%".$request->name."%");

                    $append['name'] = $request->name;
                }

            });


            if ( $request->status != "" ) {
                $query->where('status', $request->status);
                $append['status'] = $request->status;
            }

            if ( $request->country != "" ) {
                $query->where('country_id', $request->country);
                $append['country'] = $request->country;
            }

            if ( $request->color != "" ) {
                $query->where('color', $request->color);
                $append['color'] = $request->color;
            }

            if ( $request->size != "" ) {
                $query->where('size', $request->size);
                $append['size'] = $request->size;
            }



            if (( $request->fromDate !== null ) && $request->toDate == null) {
                $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime(Carbon::now()))]);
                $append['fromDate']  = $request->fromDate;
            }

            if (( $request->fromDate !== null) && ($request->has('toDate') && $request->toDate !== null)) {
                $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime($request->toDate))]);
                $append['fromDate']  = $request->fromDate;
                $append['toDate']  = $request->toDate;
            }



        })->latest()->paginate(10)->appends($append)->withPath('/international/paginate/filters');


        $status = Status::get(['id', 'name']);

        $brands = Brand::where('status', 1)->get(['id', 'name']);
        $countries = Country::get(['id', 'name']);

        $colors = Additional::distinct()->get('color');
        $sizes = Additional::distinct()->get('size');


        return Inertia::render('international/index', [
            'menuAccess' => $menuAccess,
            'internationals' => $internationals,
            'brands' => $brands,
            'countries' => $countries,
            'colors' => $colors,
            'sizes' => $sizes,
            'status' => $status,
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

        $menuAccess = AccessUser::where('user_id', Auth::user()->id)->where('menu_id', $this->menuId)->get([
            'user_id',
            'menu_id',
            'action_id'
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

        $menuAccess = AccessUser::where('user_id', Auth::user()->id)->where('menu_id', $this->menuId)->get([
            'user_id',
            'menu_id',
            'action_id'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $order)
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

        $single = InternationalOrder::with(['country', 'order_status'])->where('order_no', $order)->first();
        $status = Status::get();

        return Inertia::render('international/index', [
            'menuAccess' => $menuAccess,
            'single' => $single,
            'status' => $status,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 3);

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
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $orderId)
    {
        $checkPermission = Permission::access($request, $this->menuId, 3);

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

        $single = InternationalOrder::with(['country', 'order_status'])->where('order_no', $orderId)->first();
        $status = Status::get();

        $update = InternationalOrder::where('order_no', $orderId)->update(['status' => $request->status, 'description' => $request->description]);

    }


    public function print(Request $request, string $orderId){

        $single = InternationalOrder::with(['country', 'order_status'])->where('order_no', $orderId)->first();

        return Inertia::render('international/print', [
            'single' => $single
        ]);
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

        $menuAccess = AccessUser::where('user_id', Auth::user()->id)->where('menu_id', $this->menuId)->get([
            'user_id',
            'menu_id',
            'action_id'
        ]);
    }
}
