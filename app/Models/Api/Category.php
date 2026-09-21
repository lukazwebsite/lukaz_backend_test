<?php
namespace App\Models\Api;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $guarded = [];

    /**
     * Parent category relationship
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'id');
    }

    /**
     * Child categories relationship
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }


    // All descendants, eager‑loaded recursively
    public function childs()
    {
        return $this->children()->with('childs');
    }


    // many to many relationships
    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}
