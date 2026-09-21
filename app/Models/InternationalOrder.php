<?php

namespace App\Models;

use App\Models\Api\Country;
use Illuminate\Database\Eloquent\Model;

class InternationalOrder extends Model
{
    protected $table = 'international_orders';
    protected $guarded = [];

    public function country(){
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function order_status(){
        return $this->belongsTo(Status::class, 'status', 'id');
    }
}
