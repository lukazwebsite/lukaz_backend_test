<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingInfo extends Model
{
    use HasFactory;

    protected $table = 'shipping_info';

    protected $fillable = [
        'order_no','district_id','thana','full_name','phone','address','note'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_no');
    }


    public function district(){
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

}
