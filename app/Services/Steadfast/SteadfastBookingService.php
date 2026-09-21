<?php

namespace App\Services\Steadfast;

use App\Models\Admin\OrderConsignment;
use App\Models\Api\Order;
use App\Models\Api\OrderTracking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Booking and status sync for Steadfast, in one place so the controller and
 * the scheduled command cannot drift apart.
 */
class SteadfastBookingService
{
    protected $client;
    protected $mapper;

    public function __construct(SteadfastClient $client, SteadfastOrderMapper $mapper)
    {
        $this->client = $client;
        $this->mapper = $mapper;
    }

    /**
     * Book one order.
     *
     * The row is inserted before the API call so the unique index on
     * (courier, order_no) rejects a concurrent second attempt. If the call
     * then fails the placeholder row is removed, leaving the order bookable
     * again.
     *
     * @param  Order  $order
     * @param  int|null  $userId
     * @return OrderConsignment
     */
    public function book(Order $order, $userId = null)
    {
        $this->guardEnabled();

        $existing = OrderConsignment::where('courier', 'steadfast')
            ->where('order_no', $order->order_no)
            ->first();

        if ($existing) {
            throw new SteadfastException(
                'Order ' . $order->order_no . ' is already with Steadfast (consignment '
                . ($existing->consignment_id ?: 'pending') . ').'
            );
        }

        // 6 = Cancel, 7 = Rejected. Never hand a dead order to a rider.
        if (in_array((int) $order->status, [6, 7], true)) {
            throw new SteadfastException('Order ' . $order->order_no . ' is cancelled or rejected.');
        }

        $payload = $this->mapper->build($order);

        try {
            $consignment = OrderConsignment::create([
                'courier' => 'steadfast',
                'order_no' => $order->order_no,
                'invoice' => $payload['invoice'],
                'cod_amount' => $payload['cod_amount'],
                'request_payload' => $payload,
                'created_by' => $userId,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique index tripped: another request booked it first.
            throw new SteadfastException('Order ' . $order->order_no . ' is already being sent to Steadfast.');
        }

        try {
            $result = $this->client->createOrder($payload);
        } catch (SteadfastException $e) {
            $consignment->delete();

            throw $e;
        }

        $consignment->update([
            'consignment_id' => $result['consignment_id'] ?? null,
            'tracking_code' => $result['tracking_code'] ?? null,
            'delivery_status' => $result['status'] ?? 'pending',
            'response_payload' => $result,
            'last_synced_at' => now(),
        ]);

        // Booking a courier means the parcel has left: mark it Shipped (5).
        $this->moveOrderStatus($order, 5, $userId);

        return $consignment->fresh();
    }

    /**
     * Refresh one consignment from Steadfast and move the order if the
     * delivery status maps to a local status.
     *
     * @return OrderConsignment
     */
    public function sync(OrderConsignment $consignment, $userId = null)
    {
        $this->guardEnabled();

        if (!empty($consignment->consignment_id)) {
            $response = $this->client->statusByConsignment($consignment->consignment_id);
        } else {
            $response = $this->client->statusByInvoice($consignment->invoice);
        }

        $status = $response['delivery_status'] ?? null;

        if (empty($status)) {
            $consignment->update(['last_synced_at' => now()]);

            return $consignment;
        }

        $changed = $status !== $consignment->delivery_status;

        $consignment->update([
            'delivery_status' => $status,
            'last_synced_at' => now(),
        ]);

        if ($changed) {
            $statusId = config('steadfast.status_map.' . $status);

            if ($statusId && $consignment->order) {
                $this->moveOrderStatus($consignment->order, (int) $statusId, $userId);
            }
        }

        return $consignment;
    }

    /**
     * Update the order status and append to the tracking timeline, but only
     * when the status actually changes, so the customer facing history does
     * not fill with duplicate rows on every poll.
     */
    protected function moveOrderStatus(Order $order, $statusId, $userId = null)
    {
        if ((int) $order->status === (int) $statusId) {
            return;
        }

        DB::transaction(function () use ($order, $statusId, $userId) {
            $order->update(['status' => $statusId, 'updated_by' => $userId]);

            OrderTracking::create([
                'order_no' => $order->order_no,
                'status_id' => $statusId,
                'updated_by' => $userId,
            ]);
        });

        Log::info('Steadfast moved order status', [
            'order_no' => $order->order_no,
            'status_id' => $statusId,
        ]);
    }

    protected function guardEnabled()
    {
        if (!config('steadfast.enabled')) {
            throw new SteadfastException('The Steadfast integration is turned off.');
        }
    }
}
