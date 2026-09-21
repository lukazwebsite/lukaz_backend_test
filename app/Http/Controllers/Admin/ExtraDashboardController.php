<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Branch;
use App\Models\Api\Order;
use App\Models\InternationalOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExtraDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $now = date('Y-m-d', strtotime(now()));
        $orders = Order::withCount('items')->with('status')->where('order_type', '!=', 2)->orderBy('id', 'desc')->whereBetween('created_at', [date('Y-m-01 00:00:00', strtotime(now())),  $now.' 23:59:59'])->paginate(20)->onEachSide(1);

        $total_order = Order::where('order_type', '!=', 2)->whereBetween('created_at', [date('Y-m-01 00:00:00', strtotime(now())),  $now.' 23:59:59'])->count();
        $today_orders = Order::whereBetween('created_at', [$now.' 00:00:00',  $now.' 23:59:59'])->where('order_type', '!=', 2)->count();
        $dilivered = Order::where('order_type', '!=', 2)->where('status', 8)->count();
        $cancel = Order::where('order_type', '!=', 2)->where('status', 6)->count();
        $today_offline_sale = Order::whereBetween('created_at', [$now.' 00:00:00',  $now.' 23:59:59'])->where('order_type', 2)->count();
        $international = InternationalOrder::count();

        $checkStocks = Branch::withSum('stocks', 'stock')->where('status', 1)->get();

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

       // Outlet
        $outlets = Branch::with(['orders' => function($q) use($start, $end) {
            $q->whereBetween('created_at', [$start .'00:00:00', $end.' 23:59:59']);
        }])->where('status', 1)->get();

        $graphs = [];
        $datas  = [];
        $finalData = [];

        $graphs[0]['label'] = "Online";

        foreach ($outlets as $outlet) {

            $graphs[$outlet->id]['label'] = $outlet->name;

            foreach ($outlet->orders as $order) {

                $day = date('j', strtotime($order->created_at));
                $type = $order?->order?->order_type;

                if ($type == 1) {
                    $datas[0][$day][] = $order->quantity; // Online
                } else {
                    $datas[$outlet->id][$day][] = $order->quantity;
                }
            }
        }



        foreach ($graphs as $k => $graph) {

            $finalData[$k]['label'] = $graph['label'];

            for ($i = 1; $i <= 31; $i++) {
                $finalData[$k]['data'][$i] = isset($datas[$k][$i])
                    ? array_sum($datas[$k][$i])
                    : 0;
            }
        }




       return Inertia::render('ExtraDashboard',
            [
                'orders' => $orders,
                'total_order' => $total_order,
                'today_orders' => $today_orders,
                'dilivered' => $dilivered,
                'cancel' => $cancel,
                'today_offline_sale' => $today_offline_sale,
                'international' => $international,
                'graphs' => $finalData,
                'check_stocks' => $checkStocks,
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function orders()
    // {
    //     $orders = Order::where('order_type', '!=', 2)->orderBy('id', 'desc')->paginate(20);
    //     return Inertia::render('Dashboard',
    //         [
    //             'orders' => $orders
    //         ]
    //     );
    // }

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
