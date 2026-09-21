<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Courier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CourierController extends Controller
{

    private $menuId = 14;

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
            'fromDate' => $request->fromDate,
            'toDate'   => $request->toDate
        ];

        $courier = Courier::where(function($query) use($request) {

            if ( $request->name != "" ) {
                $query->where('name', "LIKE", "%".$request->name."%");
                $query->orWhere('courier_charge', "LIKE", "%".$request->name."%");



            }


            if (( $request->fromDate !== null ) && $request->toDate == null) {
                $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime(Carbon::now()))]);
                $append['fromDate']  = $request->fromDate;
            }

            if (( $request->fromDate !== null) && ($request->has('toDate') && $request->toDate !== null)) {
                $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime($request->toDate))]);
            }



        })->latest()->paginate(10)->appends($append)->withPath('/courier/paginate/filters');





        return Inertia::render('courier/index', [
            'couriers' => $courier,
            'menuAccess' => $menuAccess,
            'append' => $append,
            'checkPermission' => $checkPermission
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

        $courier = Courier::where('id', $id)->first();

        return Inertia::render('courier/edit', [
            'courier' => $courier
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 3);

        if (!$checkPermission) {

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $data['courier_charge'] = $request->charge != '' ?  $request->charge : 0;

        Courier::where('id', $id)->update($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Courier $courier)
    {
        //
    }
}
