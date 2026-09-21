<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class ShopBy extends Model
{
    //
    protected $table = 'shop_by';



    protected $fillable = [
        'name',
        'url',
        'categories',
        'description',
        'thumbnail',
        'status',
        'created_by',
        'updated_by',
      ];

    protected $casts = [
        // 'name' => 'json',
        'categories' => 'json',
    ];
}
