<?php

namespace App\Models\Api;

use App\Models\Admin\Branch;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_no', 'user_id', 'status', 'order_type', 'discount', 'additional_discount', 'quantity',
        'shipping_cost', 'total', 'grand_total', 'payment_status', 'promo_code', 'payment_method',
        'description', 'created_by', 'updated_by', 'branch_id'
    ];


    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_no', 'order_no');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function shippingInfo()
    {
        return $this->hasOne(ShippingInfo::class, 'order_no', 'order_no');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'order_no', 'order_no');
    }

    public function trackings()
    {
        return $this->hasMany(OrderTracking::class, 'order_no', 'order_no');
    }

    public function coupon()
    {

        return $this->belongsTo(Coupon::class, 'promo_code', 'slug');
    }

    public function status(){
        return $this->belongsTo(Status::class, 'status', 'id');
    }


    public function branch(){
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

}

