<?php

namespace App\Models\Api;

use App\Models\Api\Product;
use App\Models\Api\Category;
use Illuminate\Database\Eloquent\Model;

class CategoryProduct extends Model
{
    //
        protected $table = 'category_product';

   // Relation with Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relation with Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
