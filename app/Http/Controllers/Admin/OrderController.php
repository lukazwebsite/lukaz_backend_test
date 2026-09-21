<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Stock;
use App\Models\Api\Order;
use App\Models\Api\OrderItem;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SMSCongroller;
use Inertia\Inertia;
use App\Models\Admin\Additional;
use App\Models\Admin\Branch;
use App\Models\Admin\Brand;
use App\Models\Admin\Category;
use App\Models\Api\OrderTracking;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Date;

class OrderController extends Controller
{

    private $orderText = "Hey [Customer], your order #[OrderNumber] is confirmed. Total charged: $[Amount] BDT. Track your order here: [Link]";

    private $menuId = 11;
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
        $toDate = Date('Y-m-d');

        $branches = Branch::get(['id', 'name']);


        $query = Order::withCount('items')->with(['status', 'user', 'items.product'])

        ->where(function($q) use($request){



            if ($request->user()->role_id != 1) {

                $q->where('branch_id', Auth::user()->branch_id);

            }elseif(isset($request->branch) && $request->branch !== null){

                $q->where('branch_id', $request->branch['id']);

                $append['branch']  = $request->branch;


            }


        })


        ->where(function($q) use($request){
            if (isset($request->order_type) && $request->order_type != 2) {
                $q->where('order_type', $request->order_type);

                $append['order_type']  = $request->order_type;
            }else{

                $q->where('order_type', '!=', 2);
            }

        })

        ->orderBy('created_at', 'desc')->orderBy('grand_total', 'desc');

        if ($request->has('name') && $request->name !== null) {

            if (!empty($request->name)) {
                $query->where('order_no', 'LIKE', '%'.$request->name.'%');
            }

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('users as U')
                    ->whereRaw('U.id = orders.user_id');

                if (!empty($request->name)) {
                    $sub->orWhere('mobile', 'LIKE', '%'.$request->name.'%');
                    $sub->orWhere('name', 'LIKE', '%'.$request->name.'%');
                }
            });

            $append['name']  = $request->name;
        }


        if ($request->has('brand') && $request->brand !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->brand)) {
                    $sub->where('brand_id', $request->brand['id']);
                }
            });

            $append['brand']  = $request->brand;
        }

        if ($request->has('color') && $request->color !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->color)) {
                    $sub->where('color', $request->color);
                }
            });

            $append['color']  = $request->color;
        }

        if ($request->has('size') && $request->size !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->size)) {
                    $sub->where('size', $request->size);
                }
            });

            $append['size']  = $request->size;
        }


        if ($request->has('category') && $request->category !== null) {
            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->join('products as p', 'ot.item_id', '=', 'p.id')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->category)) {
                    $sub->whereJsonContains('p.category_ids', $request->category['id']);
                }
            });

            $append['category'] = $request->category;
        }


        if (!empty($request->status)) {
            $query->where('status', $request->status);

            $append['status']  = $request->status;
        }


        if (!empty($request->fromDate) && !isset($request->toDate)) {
            $query->whereBetween('created_at', [date('Y-m-d 00:00:00', strtotime($request->fromDate)), $toDate.' 23:59:59']);

            $append['fromDate']  = $request->fromDate;
            $append['toDate']  = $request->toDate;
        }

        if (!empty($request->fromDate) && !empty($request->toDate)) {
            $query->whereBetween('created_at', [date('Y-m-d 00:00:00', strtotime($request->fromDate)), date('Y-m-d 00:00:00', strtotime($request->toDate))]);

            $append['fromDate']  = $request->fromDate;
            $append['toDate']  = $request->toDate;
        }


        $total = $query->count();



        $data = $query->take(ceil($total * 0.2))->get();


        $perPage = 20; // Number of items per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $items = $data->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $orders = new LengthAwarePaginator(
            $items,
            $data->count(), // total = 20
            $perPage,
            $currentPage,
            ['path' => url('/order/paginate/filters'), 'query' => request()->query()]
        );




        // $orders = $query->paginate(20)->onEachSide(1)->appends($append)->withPath('/order/paginate/filters');


        $status = Status::get(['id', 'name']);
        $categories = Category::where('status', 1)->get(['id', 'name']);
        $brands = Brand::where('status', 1)->get(['id', 'name']);

        $colors = Additional::distinct()->get('color');
        $sizes = Additional::distinct()->get('size');



        return Inertia::render('order/index', [
            'menuAccess' => $menuAccess,
            'orders' => $orders,
            'status' => $status,
            'categories' => $categories,
            'brands' => $brands,
            'branches' => $branches,
            'colors' => $colors,
            'sizes' => $sizes,
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

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }
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
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $id)
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
        $toDate = Date('Y-m-d');

        $branches = Branch::get(['id', 'name']);


        $query = Order::withCount('items')->with(['status', 'user', 'items.product', 'transactions'])->where('status', $id)
                ->orderBy('created_at', 'desc')->orderBy('grand_total', 'desc');


        $query->where(function($q) use($request){

           if ($request->user()->role_id != 1) {

                $q->where('branch_id', Auth::user()->branch_id);

            }elseif(isset($request->branch) && $request->branch !== null){

                $q->where('branch_id', $request->branch['id']);

                $append['branch']  = $request->branch;


            }
        })


        ->where(function($q) use($request){
            if (isset($request->order_type) && $request->order_type != 2) {
                $q->where('order_type', $request->order_type);

                $append['order_type']  = $request->order_type;
            }else{

                $q->where('order_type', '!=', 2);
            }

        });

        if ($request->has('name') && $request->name !== null) {

            if (!empty($request->name)) {
                $query->where('order_no', 'LIKE', '%'.$request->name.'%');
            }

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('users as U')
                    ->whereRaw('U.id = orders.user_id');

                if (!empty($request->name)) {
                    $sub->orWhere('mobile', 'LIKE', '%'.$request->name.'%');
                    $sub->orWhere('name', 'LIKE', '%'.$request->name.'%');
                }
            });

            $append['name']  = $request->name;
        }


        if ($request->has('brand') && $request->brand !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->brand)) {
                    $sub->where('brand_id', $request->brand['id']);
                }
            });

            $append['brand']  = $request->brand;
        }

        if ($request->has('color') && $request->color !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->color)) {
                    $sub->where('color', $request->color);
                }
            });

            $append['color']  = $request->color;
        }

        if ($request->has('size') && $request->size !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->size)) {
                    $sub->where('size', $request->size);
                }
            });

            $append['size']  = $request->size;
        }


        if ($request->has('category') && $request->category !== null) {
            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->join('products as p', 'ot.item_id', '=', 'p.id')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->category)) {
                    $sub->whereJsonContains('p.category_ids', $request->category['id']);
                }
            });

            $append['category'] = $request->category;
        }


        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }


        if (!empty($request->fromDate) && !isset($request->toDate)) {
            $query->whereBetween('created_at', [date('Y-m-d 00:00:00', strtotime($request->fromDate)), $toDate.' 23:59:59']);
        }

        if (!empty($request->fromDate) && !empty($request->toDate)) {
            $query->whereBetween('created_at', [date('Y-m-d 00:00:00', strtotime($request->fromDate)), date('Y-m-d 00:00:00', strtotime($request->toDate))]);
        }


        $total = $query->count();

        $data = $query->take(ceil($total * 0.2))->get();


        $perPage = 20; // Number of items per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $items = $data->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $orders = new LengthAwarePaginator(
            $items,
            $data->count(), // total = 20
            $perPage,
            $currentPage,
            ['path' => url('/order/paginate/filters/'.$id.'/show'), 'query' => request()->query()]
        );




        // $orders = $query->paginate(20)->onEachSide(1)->appends($append)->withPath('/order/paginate/filters/'.$id.'/show');
        $status = Status::get(['id', 'name']);
        $categories = Category::where('status', 1)->get(['id', 'name']);
        $brands = Brand::where('status', 1)->get(['id', 'name']);

        $colors = Additional::distinct()->get('color');
        $sizes = Additional::distinct()->get('size');

        return Inertia::render('order/filter', [
            'filter_status' => $id,
            'menuAccess' => $menuAccess,
            'orders' => $orders,
            'status' => $status,
            'categories' => $categories,
            'branches' => $branches,
            'brands' => $brands,
            'colors' => $colors,
            'sizes' => $sizes,
            'append' => $append,
        ]);


    }


    /**
     * Display a listing of the resource.
     */
    public function preorder(Request $request, $id){

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
        $toDate = Date('Y-m-d');

        $branches = Branch::get(['id', 'name']);
        $query = Order::withCount('items')->with(['status', 'user', 'items.product', 'transactions'])->orderBy('created_at', 'desc')->orderBy('grand_total', 'desc');


        $query->where(function($q) use($request){

           if ($request->user()->role_id != 1) {

                $q->where('branch_id', Auth::user()->branch_id);

            }elseif(isset($request->branch) && $request->branch !== null){

                $q->where('branch_id', $request->branch['id']);

                $append['branch']  = $request->branch;


            }
        })

        ->where('order_type', 0);

        if ($request->has('name') && $request->name !== null) {

            if (!empty($request->name)) {
                $query->where('order_no', 'LIKE', '%'.$request->name.'%');
            }

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('users as U')
                    ->whereRaw('U.id = orders.user_id');

                if (!empty($request->name)) {
                    $sub->orWhere('mobile', 'LIKE', '%'.$request->name.'%');
                    $sub->orWhere('name', 'LIKE', '%'.$request->name.'%');
                }
            });

            $append['name']  = $request->name;
        }


        if ($request->has('brand') && $request->brand !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->brand)) {
                    $sub->where('brand_id', $request->brand['id']);
                }
            });

            $append['brand']  = $request->brand;
        }

        if ($request->has('color') && $request->color !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->color)) {
                    $sub->where('color', $request->color);
                }
            });

            $append['color']  = $request->color;
        }

        if ($request->has('size') && $request->size !== null) {

            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->size)) {
                    $sub->where('size', $request->size);
                }
            });

            $append['size']  = $request->size;
        }


        if ($request->has('category') && $request->category !== null) {
            $query->whereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('order_items as ot')
                    ->join('products as p', 'ot.item_id', '=', 'p.id')
                    ->whereRaw('ot.order_no = orders.order_no');

                if (!empty($request->category)) {
                    $sub->whereJsonContains('p.category_ids', $request->category['id']);
                }
            });

            $append['category'] = $request->category;
        }


        // if (!empty($request->status)) {
        //     $query->where('status', $request->status);
        // }


        if (!empty($request->fromDate) && !isset($request->toDate)) {
            $query->whereBetween('created_at', [date('Y-m-d 00:00:00', strtotime($request->fromDate)), $toDate.' 23:59:59']);
        }

        if (!empty($request->fromDate) && !empty($request->toDate)) {
            $query->whereBetween('created_at', [date('Y-m-d 00:00:00', strtotime($request->fromDate)), date('Y-m-d 00:00:00', strtotime($request->toDate))]);
        }


        $total = $query->count();

        $data = $query->take(ceil($total * 0.2))->get();


        $perPage = 20; // Number of items per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $items = $data->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $orders = new LengthAwarePaginator(
            $items,
            $data->count(), // total = 20
            $perPage,
            $currentPage,
            ['path' => url('/order/paginate/filters/'.$id.'/show/preorder'), 'query' => request()->query()]
        );


        // $orders = $query->paginate(20)->onEachSide(1)->appends($append)->withPath('/orders/'.$id.'/show/preorder');
        $status = Status::get(['id', 'name']);
        $categories = Category::where('status', 1)->get(['id', 'name']);
        $brands = Brand::where('status', 1)->get(['id', 'name']);

        $colors = Additional::distinct()->get('color');
        $sizes = Additional::distinct()->get('size');

        return Inertia::render('order/preorder', [
            'filter_status' => $id,
            'menuAccess' => $menuAccess,
            'orders' => $orders,
            'branches' => $branches,
            'status' => $status,
            'categories' => $categories,
            'brands' => $brands,
            'colors' => $colors,
            'sizes' => $sizes,
            'append' => $append,
        ]);
    }



    /**
     * Display a listing of the resource.
     */
    public function view(Request $request, $order)
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


        $order = Order::withCount('items')->with(['status', 'transactions', 'items' => function($q){
            $q->withTrashed();
        }, 'items.stock.branchs', 'shippingInfo', 'shippingInfo.district', 'user'])->where('order_no', $order)->first();


        $status = Status::get();

        // Courier booking for this order, if it has already been sent. Null
        // means the Send to Steadfast action is still available.
        $consignment = $order
            ? \App\Models\Admin\OrderConsignment::where('courier', 'steadfast')
                ->where('order_no', $order->order_no)
                ->first()
            : null;

        return Inertia::render('order/view', [
            'menuAccess' => $menuAccess,
            'orders' => $order,
            'status' => $status,
            'consignment' => $consignment,
            'steadfastEnabled' => (bool) config('steadfast.enabled'),
        ]);
    }


    /**
     * Display a listing of the resource.
     */
    public function print(Request $request, $order)
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


        $order = Order::withCount('items')->with(['status', 'branch', 'items' => function($q){
            $q->withTrashed();
        }, 'items.stock.branchs', 'transactions', 'shippingInfo', 'shippingInfo.district', 'user'])->where('order_no', $order)->first();
        $status = Status::get();

        return Inertia::render('order/print', [
            'menuAccess' => $menuAccess,
            'orders' => $order,
            'status' => $status,
        ]);
    }




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request,string $id)
    {
        $checkPermission = Permission::access($request, $this->menuId, 3);

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $orderNo)
    {

       $checkPermission = Permission::access($request, $this->menuId, 3);

        if(!$checkPermission){

            return Inertia::render('auth/Unauthorize', [
                'message' => 'You do not have permission to access this page.',
            ]);
        }


        $checkStatus = Order::with('user')->where('order_no', $orderNo)->first();

        if($checkStatus->status == 8){

            return false;

        }


        // Order Tracking
        OrderTracking::create([
            'order_no' => $orderNo,
            'status_id' => $request->status_id,
        ]);


        if($request->status_id == 2 && $checkStatus->status != 2){



            foreach($request->branch_data as $k => $branch){
                Order::where('order_no', $orderNo)->update(['status' => $request->status_id, 'branch_id' => $branch]);
                $orderItem = OrderItem::where('id', $k)->where('order_no', $orderNo)->first(['quantity', 'id', 'additional_key']);
                $orderItem->update(['branch_id' => $branch]);

                Stock::where('additional_key', $orderItem->additional_key)->where('branch_id', $branch)->decrement('stock', $orderItem->quantity);
            }

            // Replace all dynamic value
            $orderText = str_replace("[OrderNumber]", $orderNo, $this->orderText);
            $customerText = str_replace("[Customer]", $checkStatus->user->name, $orderText);
            $totalText = str_replace("$[Amount]", $checkStatus->grand_total, $customerText);

            // Send confirmation sms
            $sms = new SMSCongroller();
            $sms->forgetPassword($totalText, $orderNo, $checkStatus->user->mobile);



        }elseif(($checkStatus->status != 1 && ($request->status_id == 6 && $checkStatus->status != 6) || ($request->status_id == 7 && $checkStatus->status != 7)) ){

           foreach($request->branch_data as $k => $branch){
                $orderItem = OrderItem::where('id', $k)->where('order_no', $orderNo)->first(['quantity', 'id', 'additional_key']);
                Stock::where('additional_key', $orderItem->additional_key)->where('branch_id', $branch)->increment('stock', $orderItem->quantity);
                Order::where('order_no', $orderNo)->update(['status' => $request->status_id]);
            }

        }else{

            Order::where('order_no', $orderNo)->update(['status' => $request->status_id]);
        }


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

        $getData = OrderItem::where('id', $id)->first();
        Order::where('order_no', $getData->order_no)->decrement('grand_total', $getData->current_price);
        $getData->delete();


    }
}
