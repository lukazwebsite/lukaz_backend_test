<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class BulkDiscount extends Model
{
    protected $table = 'bulk_discounts';
    protected $guarded = [];

    const TARGET_CATEGORY = 'category';
    const TARGET_PRODUCT  = 'product';

    const PRIORITY_CATEGORY = 1;
    const PRIORITY_PRODUCT  = 2;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'bulk_discount_categories', 'bulk_discount_id', 'category_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bulk_discount_products', 'bulk_discount_id', 'product_id');
    }

    public function categoryLinks()
    {
        return $this->hasMany(BulkDiscountCategory::class, 'bulk_discount_id', 'id');
    }

    public function productLinks()
    {
        return $this->hasMany(BulkDiscountProduct::class, 'bulk_discount_id', 'id');
    }

    /**
     * Campaigns that are enabled and inside their window right now.
     */
    public function scopeRunning($query, $now = null)
    {
        $now = $now ?: now();

        return $query->where('status', 1)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now);
    }

    /**
     * Derived state. The datetime range is the source of truth, never a stored column.
     */
    public function getStateAttribute()
    {
        if (!$this->status) {
            return 'disabled';
        }

        $now = now();

        if ($this->starts_at > $now) {
            return 'scheduled';
        }

        if ($this->ends_at < $now) {
            return 'expired';
        }

        return 'active';
    }
}
