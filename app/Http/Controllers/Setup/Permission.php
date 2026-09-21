<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\Admin\AccessUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Permission extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public static function access(Request $request, $menuId = null, $actionId = null)
    {



        $success = AccessUser::where('user_id', Auth::user()->id)->where('menu_id', $menuId)->where('action_id', $actionId)->count();
        // Check User Access or Not ** Customize Permission Check **
        if($success > 0 || $request->user()->role_id == 1){

            return true;

        }

        return false;
    }

}
