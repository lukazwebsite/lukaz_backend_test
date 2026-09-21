<?php

namespace App\Http\Controllers\Admin\Manual;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\Branch;
use App\Models\Admin\Brand;
use App\Models\Admin\Category;
use App\Models\Admin\Stock;
use App\Models\Api\District;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\SMSCongroller;
use App\Models\Admin\Additional;
use App\Models\Api\Order;
use App\Models\Api\OrderItem;
use App\Models\Api\OrderTracking;
use App\Models\Api\ShippingInfo;
use App\Models\Api\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

use function PHPSTORM_META\type;

class ManualController extends Controller
{

    private $menuId = 36;
    private $orderText = "Hey [Customer], your order #[OrderNumber] is confirmed. Total charged: $[Amount] BDT. Track your order here: [Link]";

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

        $append = [];

        $branch = Branch::where('id', Auth::user()->branch_id)->first(['id', 'name', 'address']);
        $branchs = Branch::where('status', 1)->get(['id', 'name', 'address']);
        $categories = Category::where('status', 1)->get(['id', 'name']);
        $brands = Brand::where('status', 1)->get(['id', 'name']);
        $users = User::where('status', 1)->get(['id', 'name', 'mobile']);
        $districts = District::get(['id', 'name', 'bn_name']);

        $colors = Additional::distinct()->get('color');
        $sizes = Additional::distinct()->get('size');

        $products = Stock::with(['product', 'media', 'branchs'])->orderBy('stock', 'desc')->where('branch_id', '!=', 1)->paginate(24)
            ->appends($append)->withPath('/pos/paginate/filters');

        return Inertia::render('manual/index', [
            'branch' => $branch,
            'categories' => $categories,
            'brands' => $brands,
            'customers' => $users,
            'products' => $products,
            'branchs' => $branchs,
            'districts' => $districts,
            'colors' => $colors,
            'sizes' => $sizes,
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
        $products = Stock::select('stocks.*')->join('products', 'products.id', '=', 'stocks.product_id')
            ->with(['product', 'media', 'branchs'])->orderBy('stock', 'desc')->where('branch_id', '!=', 1)

            ->when($request->barcode, function ($q, $barcode) {
                $q->where(function ($sub) use ($barcode) {
                    $sub->whereAny(['stocks.size', 'stocks.color', 'stocks.barcode', 'stocks.sku', 'products.name'], 'LIKE', "%{$barcode}%");

                });
            })
            ->when(
                $request->category,
                fn($q, $category) =>
                $q->whereRaw("JSON_CONTAINS(products.category_ids, ?)", ['"' . $category . '"'])
            )

            ->when(
                $request->branchs,
                fn($q, $branch) =>
                $q->where("branch_id", $branch)
            )

            ->when(
                $request->size_id,
                fn($q, $size_id) =>
                $q->where("stocks.size", $size_id)
            )

            ->when(
                $request->brand,
                fn($q, $brand) =>
                $q->where('products.brand_id', $brand)
            )
            ->paginate(24)
            ->appends($request->all())->withPath('/pos/paginate/filters');

        return response()->json($products);
    }


    /**
     * Manula sale window
     * with customer data
     */



    public function sale(Request $request)
    {


        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required',
            'district' => 'required',
            'confirm_branch_id' => 'required',
            'thana' => 'required',
            'address' => 'required',
            'pay' => 'required',

        ]);

        $uniqueOrderNumber = new OrderController();

        // dd($request->all());
        DB::beginTransaction();

        try {

            $user = $user = User::where('mobile', $request->phone_number)->first();
            // Check user login or not
            if (empty($user)) {

                if ($user) {
                    $user->tokens()->delete();

                } else {
                    $user = User::create([
                        'name' => $request->name,
                        'email' => $request->phone_number . '@example.com',
                        'mobile' => $request->phone_number,
                        'password' => '12345678',
                    ]);
                }
            }


            $userID = $user->id;



            $orderNo = 'LSM-' . $uniqueOrderNumber->getUniqueOrderNo('orders', 'order_no');

            $totalQty = 0;
            $subTotal = 0.0;

            $orderItem = [];

            foreach ($request->items as $it) {
                $lineTotal = ($it['current_price'] ?? 0) * ($it['qty'] ?? 0);
                $subTotal += $lineTotal;
                $totalQty += $it['qty'];


                $orderItem[] = [
                    'order_no' => $orderNo,
                    'item_id' => $it['id'],
                    'item_name' => $it['name'],
                    'slug' => Str::slug($it['name'], '_'),
                    'icon' => $it['image'] ?? null,
                    'color' => $it['size'] ?? null,
                    'size' => $it['color'] ?? null,
                    'additional_key' => Str::slug($it['product_id'] . '_' . $it['color'] . '_' . $it['size'], '_'),
                    'brand_id' => $it['brand_id'],
                    'branch_id' => $it['branch_id'],
                    'current_price' => $it['current_price'],
                    'regular_price' => ($it['regular_price'] > 0) ? $it['regular_price'] : 0,
                    'discount_amount' => 0,
                    'quantity' => $it['qty'],
                    'grand_total' => ($it['current_price'] ?? 0) * ($it['qty'] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];


                Stock::where('additional_key', Str::slug($it['product_id'] . '_' . $it['color'] . '_' . $it['size'], '_'))->where('branch_id', $it['branch_id'])->decrement('stock', (float) $it['qty']);



            }


            // Shipping Information
            ShippingInfo::create([
                'order_no' => $orderNo,
                'district_id' => isset($request->district['id']) ? $request->district['id'] : null,
                'thana' => isset($request->thana['id']) ? $request->thana['id'] : null,
                'full_name' => $request->name,
                'phone' => $request->phone_number,
                'address' => $request->address,
                'note' => $request->note ?? null,
            ]);



            $discount = $request->discount;

            $grandTotal = max(0, $subTotal - $discount);


            Order::create([
                'order_no' => $orderNo,
                'user_id' => $userID,
                'status' => 2, // 2 = Confirm
                'order_type' => 3,
                'discount' => $discount,
                'quantity' => $totalQty,
                'shipping_cost' => 0,
                'total' => $subTotal,
                'grand_total' => $grandTotal,
                'payment_status' => 'Due',
                'branch_id' => $request->confirm_branch_id,
                'promo_code' => null,
                'description' => $request->description ?? null,
                'created_by' => Auth::user()->id
            ]);


            // Bulk insert Order Items
            OrderItem::insert($orderItem);


            // Transaction Record
            $txnId = 'TXN-' . $uniqueOrderNumber->getUniqueOrderNo('orders', 'order_no');

            Transaction::create([
                'order_no' => $orderNo,
                'transaction_id' => $txnId,
                'user_id' => $userID ?? 0,
                'total_amount' => $grandTotal,
                'debit' => $grandTotal,
                'credit' => 0,
                'payment_method' => "Cash",
                'payment_id' => null,
                'description' => 'Manul sale pay ' . $request->pay,
            ]);

            Transaction::create([
                'order_no' => $orderNo,
                'transaction_id' => $txnId,
                'user_id' => $userID ?? 0,
                'total_amount' => $grandTotal,
                'debit' => 0,
                'credit' => $request->pay,
                'payment_method' => "Cash",
                'payment_id' => null,
                'description' => 'Manul sale pay ' . $request->pay,
            ]);

            // Order Tracking
            OrderTracking::create([
                'order_no' => $orderNo,
                'status_id' => 1,
            ]);

            OrderTracking::create([
                'order_no' => $orderNo,
                'status_id' => 2,
            ]);

            // Replace all dynamic value
            $orderText = str_replace("[OrderNumber]", $orderNo, $this->orderText);
            $customerText = str_replace("[Customer]", $request->name, $orderText);
            $totalText = str_replace("$[Amount]", $grandTotal, $customerText);

            // Send confirmation sms
            $sms = new SMSCongroller();
            $sms->forgetPassword($totalText, $orderNo, $request->phone_number);

            DB::commit();

            // $branch = Branch::where('id', Auth::user()->branch_id)->first(['id', 'name', 'contact', 'address']);
            // $order = Order::withCount('items')->with(['status', 'items' => function($q){
            //     $q->withTrashed();
            // }, 'items.stock.branchs', 'shippingInfo', 'shippingInfo.district'])->where('order_no', $orderNo)->first();

            // return Inertia::render('pos/print', [
            //     'branch' => $branch,
            //     'order' => $order,
            // ]);


        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['message' => 'Error placing order', 'error' => $e->getMessage()], 500);
        }
    }
}
