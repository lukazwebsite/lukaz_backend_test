<?php

namespace App\Models\Api;

use App\Models\Admin\Branch;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $table = 'stocks';
    protected $guarded = [];


    public function branches(){
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }



}
