<?php

namespace App\Http\Controllers\Admin\Pos;

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Additional;
use App\Models\Admin\Branch;
use App\Models\Admin\Brand;
use App\Models\Admin\Category;
use App\Models\Admin\Product;
use App\Models\Admin\Stock;
use App\Models\Api\Order;
use App\Models\Api\OrderItem;
use App\Models\Api\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Http\Controllers\SMSCongroller;
use App\Models\Admin\BranchDailyCounter;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class ExtraPosController extends Controller
{

    private $menuId = 10;
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

        $append = [];

        $branch = Branch::where('id', Auth::user()->branch_id)->first(['id', 'name', 'address']);
        $categories = Category::where('status', 1)->get(['id', 'name']);
        $brands = Brand::where('status', 1)->get(['id', 'name']);
        $users = User::where('status', 1)->get(['id', 'name', 'mobile']);
        $products = Stock::with(['product', 'media'])->where('branch_id', Auth::user()->branch_id)->orderBy('stock', 'desc')
        ->paginate(24)
        ->appends($append)->withPath('/pos/paginate/filters');

        $colors = Additional::distinct()->get('color');
        $sizes = Additional::distinct()->get('size');

        return Inertia::render('extra_pos/index', [
            'branch' => $branch,
            'colors' => $colors,
            'sizes' => $sizes,
            'categories' => $categories,
            'brands' => $brands,
            'customers' => $users,
            'products' => $products,
            'title' => 'Point of Sale',
            'description' => 'Manage your point of sale operations here.',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function ajaxLoad(Request $request)
    {


        // ->paginate(16)
        $products = Stock::query()->select('stocks.*', 'products.brand_id', 'products.category_ids', 'products.name' )
        ->join('products', 'products.id', '=', 'stocks.product_id')
        ->where('stocks.branch_id', Auth::user()->branch_id)
        ->when($request->barcode, function ($q, $barcode) {
            $q->where(function ($sub) use ($barcode) {
                $sub->where('stocks.size', $barcode)
                    ->orWhere('stocks.color', $barcode)
                    ->orWhere('stocks.barcode', $barcode)
                    ->orWhere('stocks.sku',  'LIKE', "%{$barcode}%")
                    ->orWhere('products.name', 'LIKE', "%{$barcode}%"); // ✅ merged product.name
            });
        })
        ->when($request->category, fn($q, $category) =>
            $q->whereRaw("JSON_CONTAINS(products.category_ids, ?)", ['"' . $category . '"'])
        )
        ->when($request->brand, fn($q, $brand) =>
            $q->where('products.brand_id', $brand)
        )
        ->when($request->size_id, fn($q, $size_id) =>
            $q->where('stocks.size', $size_id)
        )
        ->with(['product', 'media'])
        ->orderBy('stock', 'desc')
        ->paginate(24)
        ->appends($request->all())->withPath('/pos/paginate/filters/extra');

        return response()->json($products);
    }



    /**
     * Here is go pos window
     * $url offile
     * $method get
     * $order type 2
     *
     */

    public function offline (Request $request){


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






        $query = Order::withCount('items')

        ->with('items', function($q) use($request){

            $q->whereHas('product', function($q) use($request) {
                if (!empty($request->name)) {
                    $q->where(function($q) use ($request) {
                        // $q->where('sku', 'LIKE', '%'.$request->name.'%')
                        $q->where('name', 'LIKE', '%'.$request->name.'%');
                    });
                }

            });
        })

        ->with(['status', 'user',  'branch', 'items.product' => function($q) use($request){

        }])->where('order_type', 2);

        if($request->user()->role_id != 1){

            $query->where('branch_id', Auth::user()->branch_id);
        }

        if ($request->has('name') && $request->name !== null) {

            if (!empty($request->name)) {
                $query->where('order_no', 'LIKE', '%'.$request->name.'%');
            }

            $query->orWhereExists(function($sub) use($request){
                $sub->select(DB::raw(1))
                    ->from('users as U')
                    ->whereRaw('U.id = orders.user_id');

                if (!empty($request->name)) {
                    $sub->orWhere('mobile', 'LIKE', '%'.$request->name.'%');
                    $sub->orWhere('name', 'LIKE', '%'.$request->name.'%');
                }
            });

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


        }


        if (!empty($request->fromDate) && !isset($request->toDate)) {
            $query->whereBetween('created_at', [date('Y-m-d 00:00:00', strtotime($request->fromDate)), $toDate.' 23:59:59']);
        }

        if (!empty($request->fromDate) && !empty($request->toDate)) {
            $query->whereBetween('created_at', [date('Y-m-d 00:00:00', strtotime($request->fromDate)), date('Y-m-d 23:59:59', strtotime($request->toDate))]);
        }


        $orders = $query->orderBy('created_at', 'desc')

        ->paginate(20)->onEachSide(1)->withQueryString()->withPath('/offline/paginate/filters/extra');


        $categories = Category::where('status', 1)->get(['id', 'name']);
        $brands = Brand::where('status', 1)->get(['id', 'name']);

        $colors = Additional::distinct()->get('color');
        $sizes = Additional::distinct()->get('size');
        $status = Status::get(['id','name']);


        return Inertia::render('extra_pos/offline', [
            'menuAccess' => $menuAccess,
            'orders' => $orders,
            'categories' => $categories,
            'brands' => $brands,
            'colors' => $colors,
            'sizes' => $sizes,
            'append' => $append,
            'status' => $status,
        ]);
    }



    public function sale(Request $request){

        $request->validate([
            'pay' => 'required|numeric|min:1',
            'items' => 'required|array|min:1',
        ]);

        $uniqueOrderNumber = new OrderController();


        DB::beginTransaction();

        try {



            $userID = $request->customer;


            $orderNo = $this->getUniqueOrderNo('orders', 'order_no');

            $totalQty = 0;
            $subTotal = 0.0;

            $orderItem = [];

            foreach ($request->items as $it) {
                $lineTotal = ($it['regular_price'] ?? 0) * ($it['qty'] ?? 0);
                $subTotal += $lineTotal;
                $totalQty += $it['qty'];


                $orderItem[] = [
                    'order_no' => $orderNo,
                    'item_id' => $it['id'],
                    'item_name' =>  $it['name'],
                    'slug' => Str::slug($it['name'], '_'),
                    'icon' =>  $it['image'] ?? null,
                    'color' =>  $it['color'] ?? null,
                    'size' =>  $it['size'] ?? null,
                    'additional_key' => Str::slug($it['product_id'].'_'.$it['color'].'_'.$it['size'], '_'),
                    'brand_id' =>  $it['brand_id'],
                    'current_price' =>  $it['current_price'],
                    'regular_price' =>  $it['regular_price'],
                    'discount_amount' =>  0,
                    'quantity' =>  $it['qty'],
                    'grand_total' =>  ($it['current_price'] ?? 0) * ($it['qty'] ?? 0),
                    'branch_id' =>  Auth::user()->branch_id,
                    'created_at' =>  now(),
                    'updated_at' =>  now(),
                ];

                Stock::where('additional_key', Str::slug($it['product_id'].'_'.$it['color'].'_'.$it['size'], '_'))->where('branch_id', Auth::user()->branch_id)->decrement('stock', $it['qty']);

            }



            $discount = $request->discount;

            $grandTotal = max(0, $subTotal - $discount);


            Order::create([
                'order_no' => $orderNo,
                'user_id' => $userID,
                'branch_id' =>  Auth::user()->branch_id,
                'status' => 8, // Pending
                'order_type' => 2,
                'discount' => $discount,
                'quantity' => $totalQty,
                'shipping_cost' => 0,
                'total' => $subTotal,
                'grand_total' => $grandTotal,
                'payment_status' => 'Paid',
                'payment_method' => $request->payment_method,
                'promo_code' =>  null,
                'description' => $request->description ?? null,
                'created_by' => Auth::user()->id
            ]);


            // Bulk insert Order Items
            OrderItem::insert($orderItem);


            // Transaction Record
            $txnId = 'TXN-' . $uniqueOrderNumber->getUniqueOrderNo('orders', 'order_no');
            $paid = true;

            Transaction::create([
                'order_no' => $orderNo,
                'transaction_id' => $txnId,
                'user_id' => $userID ?? 0,
                'total_amount' => $subTotal,
                'debit' => $subTotal,
                'credit' => 0,
                'payment_method' => $request->payment_method,
                'payment_id' => null,
                'description' => 'Total sale amount '.$subTotal,
            ]);

            if(!empty($request->discount) && $request->discount > 0){

                Transaction::create([
                    'order_no' => $orderNo,
                    'transaction_id' => $txnId,
                    'user_id' => $userID ?? 0,
                    'total_amount' => $request->discount,
                    'debit' => 0,
                    'credit' => $request->discount,
                    'payment_method' => "discount",
                    'payment_id' => null,
                    'description' => 'Discount Amount by sales manager'.$request->discount,
                ]);

            }

            Transaction::create([
                'order_no' => $orderNo,
                'transaction_id' => $txnId,
                'user_id' => $userID ?? 0,
                'total_amount' => $subTotal,
                'debit' => 0,
                'credit' => $request->pay,
                'payment_method' => $request->payment_method,
                'payment_id' => null,
                'description' => 'customer pay '.$request->pay,
            ]);

            DB::commit();

            $branch = Branch::where('id', Auth::user()->branch_id)->first(['id', 'name', 'contact', 'address']);
            $order = Order::withCount('items')->with(['status', 'branch', 'user', 'items' => function($q){
                $q->withTrashed();
            }, 'items.stock.branchs', 'shippingInfo', 'shippingInfo.district'])
            ->withSum('transactions', 'debit')
            ->withSum('transactions', 'credit')
            ->where('order_no', $orderNo)->first();




            $sms = new SMSCongroller();
            $message = "Hi {$order->user->name},\r\n";
            $message .= "Your purchase No# {$order->order_no}\r\n";
            $message .= "Amount: {$order->grand_total}\r\n";
            $message .= "Thank you for shopping with us!";
            $sms->sms_send($order->user->mobile, $message);


            return Inertia::render('extr_pos/print', [
                'branch' => $branch,
                'order' => $order,
            ]);


        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['message' => 'Error placing order', 'error' => $e->getMessage()], 500);
        }
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
        }, 'items.stock.branchs', 'transactions', 'shippingInfo', 'shippingInfo.district', 'user'])
        ->withSum('transactions', 'debit')
        ->withSum('transactions', 'credit')
        ->where('order_no', $order)->first();
        $status = Status::get();

        return Inertia::render('extra_pos/pos_print', [
            'menuAccess' => $menuAccess,
            'order' => $order,
            'status' => $status,
        ]);
    }






    public function getUniqueOrderNo($table)
    {

        return DB::transaction(function () {

            $branch = Branch::where('id', Auth::user()->branch_id)->first();
            $today = Carbon::today()->toDateString();

            $counter = BranchDailyCounter::where('branch_id', $branch->id)
                ->where('date', $today)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = BranchDailyCounter::create([
                    'branch_id' => $branch->id,
                    'date' => $today,
                    'last_serial' => 0
                ]);
            }

            $counter->increment('last_serial');

            return $branch->short_code .
                Carbon::today()->format('ymd') .
                str_pad($counter->last_serial, 3, '0', STR_PAD_LEFT);


        });


        // $currentDate = date('Y-m-d', strtotime(Carbon::now()));
        // $last_serial = DB::table($table)->whereBetween('created_at', [$currentDate.' 00:00:00', $currentDate.' 23:59:59'])->where('branch_id', Auth::user()->branch_id)->count();

        // $new_serial = ($last_serial + 1);

        // return date('Ymd', strtotime(Carbon::now())).str_pad($new_serial, 3, '0', STR_PAD_LEFT);;

    }




}
