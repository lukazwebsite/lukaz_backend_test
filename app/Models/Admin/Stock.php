<?php

namespace App\Models\Admin;

use App\Models\Api\OrderItem;;
use Illuminate\Database\Eloquent\Model;
use Awobaz\Compoships\Compoships;

class Stock extends Model
{

    use Compoships;

    protected $table = 'stocks';
    protected $guarded = [];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function media(){
        return $this->hasOne(Media::class, 'slug', 'slug');
    }


    public function branchs(){
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }


    public function orderitems(){
        return $this->belongsTo(OrderItem::class, ['additional_key', 'branch_id'], ['additional_key', 'branch_id']);
    }

}
