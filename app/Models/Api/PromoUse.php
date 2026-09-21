<?php

namespace App\Models\Api;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromoUse extends Model
{
    use HasFactory;

    protected $table = 'promo_use';

    protected $fillable = [
        'user_id', 'order_no','promo_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_no', 'order_no');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
