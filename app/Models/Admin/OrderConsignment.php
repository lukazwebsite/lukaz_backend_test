<?php

namespace App\Models\Admin;

use App\Models\Api\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderConsignment extends Model
{
    use HasFactory;

    protected $table = 'order_consignments';

    protected $fillable = [
        'courier', 'order_no', 'invoice', 'consignment_id', 'tracking_code',
        'cod_amount', 'delivery_status', 'last_synced_at',
        'request_payload', 'response_payload', 'created_by',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'last_synced_at' => 'datetime',
        'cod_amount' => 'double',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_no', 'order_no');
    }

    /**
     * A consignment is final when Steadfast will not change its status again.
     * The sync command uses this to stop polling.
     */
    public function isFinal()
    {
        return in_array($this->delivery_status, config('steadfast.final_statuses', []), true);
    }
}
