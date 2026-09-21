<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Additional;
use App\Models\Admin\Branch;
use App\Models\Admin\Brand;
use App\Models\Admin\Category;
use App\Models\Admin\Media;
use App\Models\Admin\Product;
use App\Models\Admin\Stock;
use App\Services\BulkDiscountResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;

class ProductController extends Controller
{
    private $menuId = 4;
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


        $query = Additional::with(['product:id,name,brand_id,category_ids,status', 'product.brand:id,name', 'product.categories', 'media'])
        ->withSum('stocks', 'stock')
        ->orderBy('id', 'desc');

        $append = $request->all();

        if (($request->has('fromDate') && $request->fromDate !== null ) && $request->toDate == null) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime(Carbon::now()))]);

        }

        if (($request->has('fromDate') && $request->fromDate !== null) && ($request->has('toDate') && $request->toDate !== null)) {
            $query->whereBetween('created_at', [date("Y-m-d 00:00:00", strtotime($request->fromDate)), date("Y-m-d 23:59:59", strtotime($request->toDate))]);

        }

       if ($request->has('color') && $request->color !== null) {
            $query->where('color', 'like', '%' . $request->color . '%');

        }

        if ($request->has('size') && $request->size !== null) {
            $query->where('size', 'like', '%' . $request->size . '%');

        }

        if ($request->has('name') && $request->name !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('products as p')
                    ->whereRaw('p.id = product_additionals.product_id');

                if (!empty($request->name)) {
                    $sub->whereAny(['p.name', 'p.sku'], 'like', '%' . $request->name . '%');
                }
            });

            $query->orWhereAny(['regular_price', 'current_price', 'discount', 'stock'], 'like', '%' . $request->name . '%');


        }



        if ($request->has('category') && $request->category !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('products as p')
                    ->whereRaw('p.id = product_additionals.product_id');

                if (!empty($request->category)) {
                    $sub->whereJsonContains('category_ids', $request->category['id']);
                }
            });


        }


        if ($request->has('brand') && $request->brand !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('products as p')
                    ->whereRaw('p.id = product_additionals.product_id');

                if (!empty($request->brand)) {
                    $sub->where('brand_id', $request->brand['id']);
                }
            });


        }



        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status );
            $append['status']  = $request->status;
        }

        $products = $query->latest()->paginate(10)->withQueryString()->withPath('/product/paginate/filters');


        $categories = Category::where('status', 1)->get(['id', 'name']);
        $brands = Brand::where('status', 1)->get(['id', 'name']);

        $colors = Additional::distinct()->get('color');
        $sizes = Additional::distinct()->get('size');


        return Inertia::render('product/index', [
            'menuAccess' => $menuAccess,
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'colors' => $colors,
            'sizes' => $sizes,
            'append' => $append,

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

        $brands = Brand::get(['id', 'name'])->sortBy('name')->values()->toArray();
        $categories = Category::get(['id', 'name'])->sortBy('name')->values()->toArray();


        return Inertia::render('product/create', [
            'brands' => $brands,
            'categories' => $categories
        ]);
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



        $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'category_ids.*' => 'required|exists:categories,id',
            'current_price' => 'required|numeric|min:0',
            'sku' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'description' => 'nullable|string',
            // Add other validation rules as needed
        ]);





        $data['name'] = $request->name;
        $data['slug'] = Str::slug($request->name);
        $data['current_price'] = $request->current_price;
        $data['regular_price'] = $request->regular_price;
        $data['discount_type'] = $request->discount_type;
        $data['discount'] = $request->discount;
        $data['brand_id'] = $request->brand_id;
        $data['status'] = (int)$request->status ? 1 : 0;
        $data['category_ids'] = $request->categories;
        $data['sku'] = $request->sku;
        $data['description'] = $request->description;

        $data['color'] = explode(',', $request->color);
        $data['size'] = explode(',', $request->size);

        $data['payment_type'] = $request->payment_type;
        $data['partial_amount'] = $request->partial_amount;
        $data['delivery_express'] = $request->delivery_express;
        $data['delivery_express_support'] =  $request->delivery_express_support;

        $data['international'] = $request->international;

        $data['pre_order_days'] = $request->pre_order_days;
        $data['pre_order_notes'] = $request->pre_order_notes;
        $data['is_pre_order'] = (int)$request->is_pre_order ? 1 : 0;

        $data['seo_title'] = $request->seo_title;
        $data['seo_description'] = $request->seo_description;
        $data['seo_keywords'] = $request->seo_keywords;

        $data['start_time'] = (isset($request->start_time)) ? date("H:i", strtotime($request->start_time)) : null;
        $data['end_time'] = (isset($request->end_time)) ? date("H:i", strtotime($request->end_time)) : null;

        $data['start_date'] = (isset($request->start_date)) ? date("Y-m-d", strtotime($request->start_date)) : null;
        $data['end_date'] = (isset($request->end_date)) ? date("Y-m-d", strtotime($request->end_date)) : null;

        $data['special_discount'] = $request->special_discount;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;




        $product = Product::create($data);
        $product->categories()->sync($request->categories);

        // Additional prodcut information goes here
        if(!empty($request->color)){
            foreach(explode(',', $request->color) as $colorKey => $color){
                if(!empty($request->size)){
                    foreach(explode(',', $request->size) as $sizeKey => $size){

                        $additionals[] = [
                            'product_id' => $product->id,
                            'additional_key' => Str::slug($product->id.'_'.$color.'_'.$size, '_'),
                            'slug' => str::slug($product->id.'_'.$color, '_'),
                            'sku' => Str::slug($product->sku.'-'.$color.'-'.$size),
                            'color' => $color,
                            'size' => $size,
                            'status' => 1,
                            'stock_status' => 0,
                            'stock' => 0,
                            'regular_price' => $product->regular_price,
                            'current_price' => $product->current_price,
                            'discount' => $product->discount,
                            'discount_type' => $product->discount_type,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'created_by' => $data['created_by'],
                            'updated_by' => $data['created_by'],
                        ];
                    }
                }

                $media[] = [
                    'product_id' => $product->id,
                    'slug' => str::slug($product->id.'_'.$color, '_'),
                    'color' => $color,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

            }
        }

        Media::insert($media);

        Additional::insert($additionals);

        // Store branch ways data
        $branches = Branch::where('status', '!=', 0)->get();
        foreach ($branches as $branch) {
            foreach ($additionals as $additional) {
                $branchData[] = array_merge($additional, [
                    'branch_id' => $branch->id
                ]);
            }
        }

       Stock::insert($branchData);

       return redirect()->route('product.additional', $product->id)->with('success', 'Product created successfully.');


    }


    /**
     * Store a newly created resource in storage.
     */
    public function medias(Request $request, $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 2);

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

        $medias = Media::where('product_id', $id)->get();

        return Inertia::render('product/media',
            [
                'medias' => $medias,
                'menuAccess' => $menuAccess,
            ]
        );
    }


    /**
     * Media update function goes here
     * @method POST
     * $parameter $product_id
     */

    public function mediaUpdate(Request $request, $id){

        $oldData = Media::where('id', $id)->first();

        if($request->hasFile('icon')){

            // Validate file first
            $request->validate([
                'icon' => 'image|mimes:jpeg,png,jpg,gif,webp|max:4148'
            ]);


            $upload = $request->file('icon');
            $name = Str::random() . '.' . $upload->getClientOriginalExtension();

            $image = Image::read($upload);
            $image->save(public_path('products').'/'.$oldData->slug.'_icon_'.$name);
            $data['color_icon'] = $oldData->slug.'_icon_'.$name;

            $iconName =  $oldData->slug.'_icon_40x40_'.$name;
            $icon = Image::read($upload)->resize(40, 40);
            $icon->save(public_path('products').'/'.$iconName);
            $data['color_icon_small'] = $iconName;

        }

        if($request->hasFile('thumbnail')){

            // Validate file first
            $request->validate([
                'thumbnail' => 'image|mimes:jpeg,png,jpg,gif,webp|max:4148'
            ]);


            $upload = $request->file('thumbnail');
            $name = Str::random() . '.' . $upload->getClientOriginalExtension();

            $image = Image::read($upload);
            $image->save(public_path('products').'/'.$oldData->slug.'_thumbnail_'.$name);
            $data['color_thumbnails'] = $oldData->slug.'_thumbnail_'.$name;

            $iconName =  $oldData->slug.'_thumbnail_40x40_'.$name;
            $icon = Image::read($upload)->resize(40, 40);
            $icon->save(public_path('products').'/'.$iconName);
            $data['color_thumbnails_small'] = $iconName;

        }




        if($request->file('galleries')){

            // Validate file first
            $request->validate([
                'galleries'   => 'array',
                'galleries.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:4148',
            ]);

            $galleryIcon = [];
            $galleries = [];

            foreach($request->file('galleries') as $key => $gallery){

                $name = $oldData->slug.'_galleries_'.Str::random() . '.' . $gallery->getClientOriginalExtension();

                $image = Image::read($gallery);
                $image->save(public_path('products').'/'.$name);

                $galleries[] = $name;


                $iconName =  $oldData->slug.'_galleries_40x40_'.Str::random() . '.' . $gallery->getClientOriginalExtension();
                $icon = Image::read($gallery)->resize(40, 40);
                $icon->save(public_path('products').'/'.$iconName);
                $galleryIcon[] = $iconName;



            }

            $data['color_galleries_small'] = $galleryIcon;
            $data['color_galleries'] = $galleries;

        }

        Media::where('id', $id)->update($data);

    }


    /**
     * Store a newly created resource in storage.
     */
    public function additional(Request $request)
    {
        $checkPermission = Permission::access($request, $this->menuId, 2);

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $additionals = Additional::where('product_id', $request->id)->get();

         $menuAccess = AccessUser::where('user_id', Auth::user()->id)->where('menu_id', $this->menuId)->get([
            'user_id',
            'menu_id',
            'action_id'
        ]);

        return Inertia::render('product/additional',
        [
            'additionals' => $additionals,
            'menuAccess' => $menuAccess,
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function additionalUpdate(Request $request, $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 3);
        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        $request->validate([
            'sku' => 'required|string|max:255',
            'additional_key' => 'required|string|max:255',
            'regular_price' => 'required|numeric|min:0',
            'current_price' => 'required|numeric|min:0',
            'color' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'discount' => 'required|string|max:255',
            'discount_type' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'stock' => 'required|string|max:255',
            'stock_status' => 'required|string|max:255',
            // Add other validation rules as needed
        ]);

        $data['sku'] = $request->sku;
        $data['additional_key'] = $request->additional_key;
        $data['regular_price'] = $request->regular_price;
        $data['current_price'] = $request->current_price;
        $data['color'] = $request->color;
        $data['size'] = $request->size;
        $data['discount'] = $request->discount;
        $data['discount_type'] = $request->discount_type;
        $data['status'] = $request->status;
        $data['stock'] = $request->stock;
        $data['stock_status'] = $request->stock_status;
        $data['barcode'] = $request->barcode;

        Additional::where('id', $id)->update($data);

        $stock['regular_price'] = $request->regular_price;
        $stock['current_price'] = $request->current_price;
        $stock['sku'] = $request->sku;

        Stock::where('additional_key', $request->additional_key)->update($stock);

        $stock['stock'] = $request->stock;

        $branch = Branch::where('status', 2)->first();

        if(!empty($branch)){

            Stock::where('additional_key', $request->additional_key)->where('branch_id', $branch->id)->update($stock);
        }



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

        $product = Product::with(['categories', 'brand'])->where('id', $id)->first();
        $brands = Brand::get(['id', 'name'])->sortBy('name')->values()->toArray();
        $categories = Category::get(['id', 'name'])->sortBy('name')->values()->toArray();


        return Inertia::render('product/edit',
        [
            'product' => $product,
            'brands' => $brands,
            'categories' => $categories
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


         $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'category_ids.*' => 'required|exists:categories,id',
            'current_price' => 'required|numeric|min:0',
            'sku' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'description' => 'nullable|string',
            // Add other validation rules as needed
        ]);

        $data['name'] = $request->name;
        $data['slug'] = Str::slug($request->name);
        $data['current_price'] = $request->current_price;
        $data['regular_price'] = $request->regular_price;
        $data['discount_type'] = $request->discount_type;
        $data['discount'] = $request->discount;
        $data['brand_id'] = $request->brand_id;
        $data['status'] = (int)$request->status ? 1 : 0;
        $data['category_ids'] = $request->categories;
        $data['sku'] = $request->sku;
        $data['description'] = $request->description;

        $data['color'] = explode(',', $request->color);
        $data['size'] = explode(',', $request->size);

        $data['payment_type'] = $request->payment_type;
        $data['partial_amount'] = $request->partial_amount;
        $data['delivery_express'] = $request->delivery_express;
        $data['delivery_express_support'] =  $request->delivery_express_support;

        $data['international'] = $request->international;

        $data['pre_order_days'] = $request->pre_order_days;
        $data['pre_order_notes'] = $request->pre_order_notes;
        $data['is_pre_order'] = (int)$request->is_pre_order ? 1 : 0;

        $data['seo_title'] = $request->seo_title;
        $data['seo_description'] = $request->seo_description;
        $data['seo_keywords'] = $request->seo_keywords;

        $data['start_time'] = (isset($request->start_time)) ? date("H:i", strtotime($request->start_time)) : null;
        $data['end_time'] = (isset($request->end_time)) ? date("H:i", strtotime($request->end_time)) : null;

        $data['start_date'] = (isset($request->start_date)) ? date("Y-m-d", strtotime($request->start_date)) : null;
        $data['end_date'] = (isset($request->end_date)) ? date("Y-m-d", strtotime($request->end_date)) : null;


        $data['special_discount'] = $request->special_discount;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;

        Product::where('id', $id)->update($data);
        $product = Product::where('id', $id)->first();

        $product->categories()->sync($request->categories);

        $additionals = [];
        $media = [];
        $branchData = [];




        // Additional prodcut information goes here
        if(!empty($request->color)){
            foreach(explode(',', $request->color) as $colorKey => $color){
                if(!empty($request->size)){
                    foreach(explode(',', $request->size) as $sizeKey => $size){

                        if(Additional::where('additional_key', Str::slug($product->id.'_'.$color.'_'.$size, '_'))->count() == 0){

                            $additionals[] = [
                                'product_id' => $product->id,
                                'additional_key' => Str::slug($product->id.'_'.$color.'_'.$size, '_'),
                                'slug' => str::slug($product->id.'_'.$color, '_'),
                                'sku' => Str::slug($product->sku.'-'.$color.'-'.$size),
                                'color' => $color,
                                'size' => $size,
                                'status' => 1,
                                'stock_status' => 0,
                                'stock' => 0,
                                'regular_price' => $product->regular_price,
                                'current_price' => $product->current_price,
                                'discount' => $product->discount,
                                'discount_type' => $product->discount_type,
                                'created_at' => now(),
                                'updated_at' => now(),
                                'created_by' => $data['created_by'],
                                'updated_by' => $data['created_by'],
                            ];

                        }
                    }
                }

                if(Media::where('slug', str::slug($product->id.'_'.$color, '_'))->count() == 0){
                    $media[] = [
                        'product_id' => $product->id,
                        'slug' => str::slug($product->id.'_'.$color, '_'),
                        'color' => $color,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }



            }
        }



        Media::insert($media);

        Additional::insert($additionals);

        // Store branch ways data
        $branches = Branch::where('status', '!=', 0)->get();
        foreach ($branches as $branch) {
            foreach ($additionals as $additional) {

                $branchData[] = array_merge($additional, [
                    'branch_id' => $branch->id
                ]);

            }
        }

       Stock::insert($branchData);

        // A price edit changes the base a running bulk discount is calculated
        // from, so re-resolve this product against whatever campaign owns it.
        // The resolver compares the new price against what it last wrote, so an
        // admin setting a price by hand releases the product from the campaign
        // instead of having their value overwritten on the next run.
        app(BulkDiscountResolver::class)->sync([$product->id]);


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
