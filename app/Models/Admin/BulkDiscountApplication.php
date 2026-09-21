<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class BulkDiscountApplication extends Model
{
    protected $table = 'bulk_discount_applications';
    protected $guarded = [];

    public function bulkDiscount()
    {
        return $this->belongsTo(BulkDiscount::class, 'bulk_discount_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
