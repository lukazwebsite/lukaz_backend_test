<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Admin\Subscribe;
use Illuminate\Http\Request;
use App\Models\Admin\AccessUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Setup\Permission;

class SubscribeController extends Controller
{


    private $menuId = 27;
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

        $query = Subscribe::query();

        $append = [];

        if ($request->has('email') && $request->email !== null) {
            $query->where('email', 'like', '%' . $request->email . '%');
            $append['email']  = $request->email;
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

        $subscribes = $query->latest()->paginate(10)->appends($append)->withPath('/subscribe/paginate/filters');

        return Inertia::render('subscribe/Index', [
            'subscribes' => $subscribes,
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
        return Inertia::render('subscribe/Create');
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

        $subscribe = Subscribe::where('id', $id)->first();

        return Inertia::render('subscribe/Edit', [
            'subscribe' => $subscribe
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


        $data['status'] = $request->status;
        $data['updated_by'] = Auth::user()->id;


        Subscribe::where('id', $id)->update($data);

        return redirect()->route('subscribe.index')->with('success', 'Subscribe updated successfully.');
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
        $subscribe = Subscribe::findOrFail($id);
        $subscribe->delete();

        return redirect()->route('subscribe.index')->with('success', 'Subscribe updated successfully.');
    }
}
