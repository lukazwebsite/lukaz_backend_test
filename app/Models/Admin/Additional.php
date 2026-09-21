<?php

namespace App\Models\Admin;

use App\Models\Api\Review;
use App\Models\Api\Stock;
use Illuminate\Database\Eloquent\Model;

class Additional extends Model
{


    protected $table = 'product_additionals';
    protected $guarded = [];


    // get sigle product name

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function media(){
        return $this->hasOne(Media::class, 'slug', 'slug');
    }

    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    public function review(){
        return $this->hasMany(Review::class, 'product_id', 'product_id');
    }

    public function stocks(){
        return $this->hasMany(Stock::class, 'additional_key', 'additional_key');
    }


}
