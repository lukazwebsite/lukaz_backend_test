<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Setup\Permission;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Menu;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccessController extends Controller
{
    private $menuId = 16;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId, 1);

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }


        $menuAccess = AccessUser::where('user_id', Auth::user()->id)->where('menu_id', $this->menuId)->get([
            'user_id',
            'menu_id',
            'action_id'
        ]);

        $staffs = User::orderBy('id', 'desc')->where('role_id', '!=', 5)->get(['id', 'name']);
        $actions = DB::table('actions')->where('status', 1)->get(['id', 'name']);
        $menus = Menu::orderBy('sequance', 'asc')->where('status', 1)->get(['id', 'title']);

        return Inertia::render('access/user', [
            'menuAccess' => $menuAccess,
            'staffs' => $staffs,
            'actions' => $actions,
            'menus' => $menus,
            'checkPermission' => $checkPermission
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 3);

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $menuAccess = AccessUser::where('user_id', $id)->get([
            'user_id',
            'menu_id',
            'action_id'
        ]);

        return response()->json($menuAccess);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 3);

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $request->validate([
            'user_id' => 'required|numeric',
            'permission' => 'required',
        ]);

        $perm = [];

        foreach($request->permission as $k => $permission){

            foreach($permission as $key => $item){

                $perm[] = [
                    'user_id' => $request->user_id,
                    'menu_id' => $k,
                    'action_id' => $key,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        AccessUser::where('user_id', $request->user_id)->delete();

        AccessUser::insert($perm);


    }

}
