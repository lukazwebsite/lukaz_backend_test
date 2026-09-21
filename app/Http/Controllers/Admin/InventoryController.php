<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Additional;
use App\Models\Admin\Branch;
use App\Models\Admin\Brand;
use App\Models\Admin\Category;
use App\Models\Admin\Stock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{

    private $menuId = 7;
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


        $append = $request->all();

        $query = Stock::with(['product', 'product.categories', 'product.brand', 'media', 'branchs']);


        if ($request->has('name') && $request->name !== null) {

            $query->where(function($q) use($request){
                $q->where('stock', 'LIKE', '%'.$request->name.'%')
                    ->orWhere('sku', 'LIKE', '%'.$request->name.'%')
                ->orWhereExists(function($sub) use($request){
                    $sub->select(DB::raw(1))
                        ->from('products as p')
                        ->whereRaw('stocks.product_id = p.id')
                        ->where('p.name', 'LIKE', '%'.$request->name.'%');
                });

            });



        }

        if (!empty($request->color)) {
            $query->where('color', 'LIKE', '%'.$request->color.'%');

        }

         if (!empty($request->size)) {
            $query->where('size', 'LIKE', '%'.$request->size.'%');

        }



        if ($request->has('brand') && $request->brand !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('products as p')
                    ->whereRaw('stocks.product_id = p.id')
                    ->where('p.brand_id', $request->brand['id']);

            });


        }

        if ($request->has('category') && $request->category !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('products as p')
                    ->whereRaw('stocks.product_id = p.id')
                    ->whereJsonContains('category_ids', $request->category['id']);
            });

            $append['category']  = $request->category;
        }

        if ($request->has('branch') && $request->branch !== null) {

            $query->where('branch_id', $request->branch['id']);


        }


        $stocks = $query->orderBy('stock', 'ASC')->paginate(15)->withQueryString();


        $categories = Category::where('status', 1)->get(['id', 'name']);
        $brands = Brand::where('status', 1)->get(['id', 'name']);

        $colors = Additional::distinct()->get('color');
        $sizes = Additional::distinct()->get('size');
        $branches = Branch::get(['id', 'name']);



        return Inertia::render('inventory/index', [
            'menuAccess' => $menuAccess,
            'categories' => $categories,
            'brands' => $brands,
            'colors' => $colors,
            'sizes' => $sizes,
            'stocks' => $stocks,
            'branches' => $branches,
            'append' => $append,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
