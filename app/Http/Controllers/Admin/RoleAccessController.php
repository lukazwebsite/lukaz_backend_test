<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessRole;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Menu;
use App\Models\Admin\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleAccessController extends Controller
{
    private $menuId = 17;
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

        $roles = Role::orderBy('id', 'desc')->where('status', 1)->get(['id', 'name']);
        $actions = DB::table('actions')->where('status', 1)->get(['id', 'name']);
        $menus = Menu::orderBy('sequance', 'asc')->where('status', 1)->get(['id', 'title']);

        return Inertia::render('access/role', [
            'menuAccess' => $menuAccess,
            'roles' => $roles,
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

        $menuAccess = AccessRole::where('role_id', $id)->get([
            'role_id',
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
            'role_id' => 'required|numeric',
            'permission' => 'required',
        ]);


        $perm = [];
        $permUser = [];

        foreach($request->permission as $k => $permission){

            foreach($permission as $key => $item){

                $perm[] = [
                    'role_id' => $request->role_id,
                    'menu_id' => $k,
                    'action_id' => $key,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // All user get from users table by role_id
        if($request->exits){

            $userIds = [];

            $users = User::where('role_id', $request->role_id)->get('id');


            foreach($users as $user){
                foreach($request->permission as $k => $permission){
                    foreach($permission as $key => $item){
                        $permUser[] = [
                            'user_id' => $user->id,
                            'menu_id' => $k,
                            'action_id' => $key,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                $userIds[] = $user->id;


            }

            AccessUser::whereIn('user_id',  $userIds)->delete();
            AccessUser::insert($permUser);


        }

        AccessRole::where('role_id', $request->role_id)->delete();
        AccessRole::insert($perm);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
