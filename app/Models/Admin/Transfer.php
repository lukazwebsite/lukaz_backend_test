<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'transfer_stocks';
    protected $guarded = [];

    public function fromBranch(){
        return $this->hasOne(Branch::class, 'id', 'from_branch_id');
    }

    public function toBranch(){
        return $this->hasOne(Branch::class, 'id', 'to_branch_id');
    }

    public function product(){
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
