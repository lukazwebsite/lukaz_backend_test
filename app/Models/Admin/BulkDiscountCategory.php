<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class BulkDiscountCategory extends Model
{
    protected $table = 'bulk_discount_categories';
    protected $guarded = [];

    public function bulkDiscount()
    {
        return $this->belongsTo(BulkDiscount::class, 'bulk_discount_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
