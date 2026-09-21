<?php

namespace App\Models\Api;


use App\Models\Api\Product;
use App\Models\Admin\Category;
use App\Models\Admin\Additional;
use Illuminate\Database\Eloquent\Model;

class ProductAdditionalGallery extends Model
{
    protected $table = 'product_additional_gallaries';
    protected $guarded = [];


    // get sigle product name

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function gallaries(){
        return $this->hasMany(ProductAdditionalGallery::class, 'product_id', 'product_id');
    }

    public function stock(){
        return $this->hasMany(Stock::class, 'slug', 'slug');
    }

    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    public function review(){
        return $this->hasMany(Review::class, 'product_id', 'product_id');
    }

     public function additional(){
           return $this->hasMany(Additional::class, 'slug', 'slug');
    }


    public function additionals(){
           return $this->hasMany(Additional::class, 'product_id', 'product_id');
    }






}
