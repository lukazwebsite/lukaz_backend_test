<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Admin\AccessUser;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\Branch;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class BranchController extends Controller
{

    private $menuId = 6;
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

        $branches = Branch::orderBy('id', 'desc')->where("status", "!=", 2)->paginate(10);

        return Inertia::render('branch/index', [
            'menuAccess' => $menuAccess,
            'branches' => $branches,
            'checkPermission' => $checkPermission
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId, 2);

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        return Inertia::render('branch/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId, 2);

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }



        $data['name'] = $request->name;
        $data['name_bn'] = $request->name_bn;
        $data['slug'] = Str::slug($request->name);
        $data['contact'] = $request->contact;
        $data['manager_phone'] = $request->manager_phone;
        $data['address'] = $request->address;
        $data['address_bn'] = $request->address_bn;
        $data['map_link'] = $request->map_link;
        $data['map_embed'] = $request->map_embed;
        $data['status'] = $request->status;

        if($request->hasFile('outlet')){

            // Create directory if not exists
            if (!File::exists(public_path('/branch'))) {
                File::makeDirectory(public_path('/branch'), 0755, true);
            }

            $upload = $request->file('outlet');
            $image = Image::read($upload);

            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();
            $data['image'] = $name;


            $icon = Image::read($upload)->resize(40, 40);
            $iconName =  'icon/'.$name;
            $data['icon'] = $iconName;

            $image->save(public_path('/branch').'/'.$name);
            $icon->save(public_path('/branch') .'/'.$iconName);

        }


        Branch::create($data);

        return redirect()->route('branch.index')->with('success', 'Branch created successfully.');


    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 1);

        if(!$checkPermission){

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

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $branch = Branch::where('id', $id)->first();

        return Inertia::render('branch/edit', [
            'branch' => $branch
        ]);
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


        $oldData = Branch::where('id', $id)->first();


        $data['name'] = $request->name;
        $data['name_bn'] = $request->name_bn;
        $data['slug'] = Str::slug($request->name);
        $data['contact'] = $request->contact;
        $data['manager_phone'] = $request->manager_phone;
        $data['address'] = $request->address;
        $data['address_bn'] = $request->address_bn;
        $data['map_link'] = $request->map_link;
        $data['map_embed'] = $request->map_embed;
        $data['status'] = $request->status;

        if($request->hasFile('outlet')){

            $filePath = public_path('branch/'.$oldData->image);
            $icon = public_path('branch/'.$oldData->icon);

            if (!empty($oldData->image) && file_exists($filePath)) {
                unlink($filePath);
                unlink($icon);
            }

            $upload = $request->file('outlet');
            $image = Image::read($upload);

            $name = $data['slug'].'_'.Str::random() . '.' . $upload->getClientOriginalExtension();
            $data['image'] = $name;


            $icon = Image::read($upload)->resize(40, 40);
            $iconName =  'icon/'.$name;
            $data['icon'] = $iconName;

            $image->save(public_path('/branch').'/'.$name);
            $icon->save(public_path('/branch') .'/'.$iconName);

        }


        Branch::where('id', $id)->update($data);

        return redirect()->route('branch.index')->with('success', 'Branch update successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 4);

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }
    }
}
