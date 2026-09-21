<?php

namespace App\Models\Admin;

use App\Models\Api\CategoryProduct;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $guarded = [];

     /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'json',
        'category_ids' => 'json',
        'color' => 'json'
    ];


    // many to many relationships
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function brand(){
        return $this->belongsTo(Brand::class);
    }

    public function stocks(){
        return $this->hasMany(Stock::class, 'product_id', 'id');
    }



}
