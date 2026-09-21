<?php

namespace App\Models\Api;

use App\Models\Admin\Additional;
use App\Models\Admin\Branch;
use App\Models\Admin\Stock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Awobaz\Compoships\Compoships;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes, Compoships;

    protected $dates = ['deleted_at'];

    protected $table = 'order_items';

    protected $fillable = [
        'order_no','branch_id','brand_id','item_id','item_name','icon','slug',
        'color','size','regular_price','current_price','discount_amount',
        'quantity','grand_total','created_by','updated_by'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_no', 'order_no');
    }


    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id', 'id');
    }


    public function additional()
    {
        return $this->belongsTo(Additional::class, 'additional_key', 'additional_key');
    }


    public function stock(){
        return $this->hasMany(Stock::class, 'additional_key', 'additional_key');
    }


    public function stockReport(){
        return $this->hasMany(Stock::class, 'id', 'item_id');
    }


    public function branchs(){
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

}
