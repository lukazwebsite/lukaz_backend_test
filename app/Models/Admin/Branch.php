<?php

namespace App\Models\Admin;

use App\Models\Api\Order;
use App\Models\Api\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    protected $table = 'branches';
    protected $guarded = [];


    public function orders(){
        return $this->hasMany(OrderItem::class);
    }

    public function orderQty(){
        return $this->hasMany(Order::class);
    }

    public function stocks(){
        return $this->hasMany(Stock::class);
    }


}
