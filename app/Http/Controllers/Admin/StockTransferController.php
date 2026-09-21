<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Setup\Permission;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Admin\AccessUser;
use App\Models\Admin\Additional;
use App\Models\Admin\Branch;
use App\Models\Admin\Stock;
use App\Models\Admin\Transfer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockTransferController extends Controller
{
    private $menuId = 5;
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


        $branchs = Branch::get(['id','name']);
        $sizes = Additional::distinct()->get('size')->toArray();
        $colors = Additional::distinct()->get('color')->toArray();

        $transfers = Transfer::with(['fromBranch', 'toBranch', 'product'])->where(function($q){
            if(Auth::user()->role_id != 1){
                $q->where('to_branch_id', Auth::user()->branch_id)->orWhere('from_branch_id', Auth::user()->branch_id);
            }
        })

        ->whereExists(function($sub) use($request){
            $sub->select(DB::raw(1))
                ->from('products as p')
                ->whereRaw('p.id = transfer_stocks.product_id');

            if (!empty($request->name)) {
                $sub->whereAny(['name', 'sku'], 'LIKE', "%$request->name%");
            }
        })

        ->where(function($q) use($request){
            if(!empty($request->fromBranchId)){
                $q->where('from_branch_id', $request->fromBranchId);
            };

            if(!empty($request->toBranchId)){
                $q->where('to_branch_id', $request->toBranchId);
            };

            if(!empty($request->color)){
                $q->where('color', $request->color);
            };

            if(!empty($request->size)){
                $q->where('size', $request->size);
            };

            if(!empty($request->fromDate) && !empty($request->toDate)){
                $fromDate = date('Y-m-d 00:00:00', strtotime($request->fromDate));
                $toDate = date('Y-m-d 23:59:59', strtotime($request->toDate));
                $q->whereBetween('created_at', [$fromDate, $toDate]);
            };


            if(!empty($request->fromDate) && !$request->toDate){
                $fromDate = date('Y-m-d 00:00:00', strtotime($request->fromDate));
                $toDate = date('Y-m-d 23:59:59', strtotime(Carbon::now()));
                $q->whereBetween('created_at', [$fromDate, $toDate]);
            };


        })


        ->orderBy('id', "DESC")->paginate(20)->withQueryString();

        return Inertia::render('transfer/index', [
            'menuAccess' => $menuAccess,
            'branchs' => $branchs,
            'sizes' => $sizes,
            'colors' => $colors,
            'transfers' => $transfers,
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


        $menuAccess = AccessUser::where('user_id', Auth::user()->id)->where('menu_id', $this->menuId)->get([
            'user_id',
            'menu_id',
            'action_id'
        ]);

        $branchs = Branch::where('status', "!=", 0)->get(['id','name']);

        return Inertia::render('transfer/create', [
            'menuAccess' => $menuAccess,

            'branchs' => $branchs,
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function getStock(Request $request, $id)
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



        $stocks = Stock::with(['product:id,name,sku'])
            ->where('branch_id', $id)
            ->get();



        $branchs = Branch::where('status', "!=", 0)->get(['id','name']);

        return Inertia::render('transfer/create', [
            'menuAccess' => $menuAccess,
            'stocks' => $stocks,
            'branchs' => $branchs,
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $stockIds = [];
        $transfer = [];



        DB::beginTransaction();

        try {

            foreach ($request->items as $k => $item) {

                $transfer = [
                    'from_branch_id'   => $item['fromBranchId'],
                    'to_branch_id'     => $item['toBranchId'],
                    'product_id'       => $item['productId'],
                    'stock_id'         => $item['stockId'],
                    'slug'             => $item['productId'].'_'.Str::slug($item['productColor'], '_').'_'.Str::slug($item['productSize'], '_'),
                    'color'            => $item['productColor'],
                    'size'             => $item['productSize'],
                    'sku'              => $item['productSku'],
                    'avaiable_stock'   => $item['avaiable'],
                    'transfer_request' => $item['qty'],
                    'status'           => $request->status,
                    'created_by'       => Auth::id(),
                    'updated_by'       => Auth::id(),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                $stockIds[] = $k;

                Additional::where('additional_key', $transfer['slug'])->decrement('stock', $item['qty']);


                $trans[] = Transfer::insertGetId($transfer);


            }


        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['message' => 'Error placing order', 'error' => $e->getMessage()], 500);
        }

        DB::commit();

        $getTransfers = Transfer::with(['toBranch', 'product'])->whereIn('id', $trans)->get();


        return Inertia::render('transfer/Print', [
            'result' => $getTransfers,
        ]);





    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $getData = Transfer::where('id', $id)->where('to_branch_id', Auth::user()->branch_id)->first();


        if(!empty($getData)){
            $checkStock = Stock::where('additional_key', $getData->slug)->where('branch_id', Auth::user()->branch_id)->increment('stock', $getData->transfer_request);

            if($checkStock){
                Transfer::where('id', $id)->update(['status' => $request->status, 'updated_by' => Auth::user()->id]);
                Stock::where('additional_key', $getData->slug)->where('branch_id', $getData->from_branch_id)->decrement('stock', $getData->transfer_request);

                return response()->json([
                    'success' => true,
                    'message' => 'Stock Received Successfully',
                ], 200);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No Data Found',
            'data' => $getData
        ], 200);


    }


}
