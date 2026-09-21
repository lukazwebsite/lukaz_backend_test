<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Setup\Permission;
use App\Models\Admin\AccessUser;
use App\Models\Admin\OrderConsignment;
use App\Models\Api\Order;
use App\Services\Steadfast\SteadfastBookingService;
use App\Services\Steadfast\SteadfastClient;
use App\Services\Steadfast\SteadfastException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SteadfastController extends Controller
{
    protected $booking;

    public function __construct(SteadfastBookingService $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Menu id is looked up by href instead of hardcoded, so the feature works
     * on any database whatever the seeded id ended up being.
     */
    private function menuId()
    {
        static $menuId = null;

        if ($menuId === null) {
            $menuId = DB::table('menus')->where('href', '/steadfast')->value('id');
        }

        return $menuId;
    }

    private function denied()
    {
        return Inertia::render('auth/Unauthorize', [
            'message' => 'You do not have permission to access this page.',
        ]);
    }

    /**
     * Consignment list with the account balance alongside, since the balance
     * is what stops bookings working when it runs out.
     */
    public function index(Request $request)
    {
        if (!Permission::access($request, $this->menuId(), 1)) {
            return $this->denied();
        }

        $menuAccess = AccessUser::where('user_id', Auth::user()->id)
            ->where('menu_id', $this->menuId())
            ->get(['user_id', 'menu_id', 'action_id']);

        $append = [
            'name' => $request->name,
            'status' => $request->status,
        ];

        $consignments = OrderConsignment::with('order')
            ->where('courier', 'steadfast')
            ->where(function ($query) use ($request) {
                if ($request->name != '') {
                    $query->where('order_no', 'LIKE', '%' . $request->name . '%')
                        ->orWhere('consignment_id', 'LIKE', '%' . $request->name . '%')
                        ->orWhere('tracking_code', 'LIKE', '%' . $request->name . '%');
                }

                if ($request->status != '') {
                    $query->where('delivery_status', $request->status);
                }
            })
            ->latest()
            ->paginate(15)
            ->appends($append)
            ->withPath('/steadfast/paginate/filters');

        return Inertia::render('steadfast/index', [
            'consignments' => $consignments,
            'menuAccess' => $menuAccess,
            'append' => $append,
            'enabled' => (bool) config('steadfast.enabled'),
            'statuses' => array_keys(config('steadfast.status_map', [])),
            'checkPermission' => true,
        ]);
    }

    /**
     * Book one order. Permission 2 = Add, because this creates a consignment
     * and spends courier balance.
     */
    public function send(Request $request, $orderNo)
    {
        if (!Permission::access($request, $this->menuId(), 2)) {
            return back()->with('error', 'You do not have permission to send orders to Steadfast.');
        }

        $order = Order::with(['shippingInfo.district'])->where('order_no', $orderNo)->first();

        if (!$order) {
            return back()->with('error', 'Order ' . $orderNo . ' was not found.');
        }

        try {
            $consignment = $this->booking->book($order, Auth::id());
        } catch (SteadfastException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Sent to Steadfast. Consignment ' . $consignment->consignment_id
            . ', tracking code ' . $consignment->tracking_code . '.');
    }

    /**
     * Book several orders in one go. Each order is booked on its own so one
     * bad address does not block the rest of the batch.
     */
    public function bulkSend(Request $request)
    {
        if (!Permission::access($request, $this->menuId(), 2)) {
            return back()->with('error', 'You do not have permission to send orders to Steadfast.');
        }

        $request->validate([
            'order_nos' => 'required|array|min:1',
            'order_nos.*' => 'required|string',
        ]);

        $sent = 0;
        $failures = [];

        $orders = Order::with(['shippingInfo.district'])
            ->whereIn('order_no', $request->order_nos)
            ->get();

        foreach ($orders as $order) {
            try {
                $this->booking->book($order, Auth::id());
                $sent++;
            } catch (SteadfastException $e) {
                $failures[] = $order->order_no . ': ' . $e->getMessage();
            }
        }

        if ($sent === 0) {
            return back()->with('error', 'Nothing was sent. ' . implode(' | ', $failures));
        }

        $message = $sent . ' order(s) sent to Steadfast.';

        if (!empty($failures)) {
            $message .= ' Skipped: ' . implode(' | ', $failures);
        }

        return back()->with('success', $message);
    }

    /**
     * Pull the current delivery status for one consignment on demand.
     */
    public function status(Request $request, $orderNo)
    {
        if (!Permission::access($request, $this->menuId(), 1)) {
            return back()->with('error', 'You do not have permission to view Steadfast status.');
        }

        $consignment = OrderConsignment::with('order')
            ->where('courier', 'steadfast')
            ->where('order_no', $orderNo)
            ->first();

        if (!$consignment) {
            return back()->with('error', 'Order ' . $orderNo . ' has not been sent to Steadfast.');
        }

        try {
            $consignment = $this->booking->sync($consignment, Auth::id());
        } catch (SteadfastException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Steadfast status: ' . $consignment->delivery_status . '.');
    }

    /**
     * Current courier balance. Returned as JSON because the UI polls it for a
     * small widget rather than a full page.
     */
    public function balance(Request $request, SteadfastClient $client)
    {
        if (!Permission::access($request, $this->menuId(), 1)) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        try {
            $response = $client->getBalance();
        } catch (SteadfastException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'balance' => $response['current_balance'] ?? null,
        ]);
    }
}
