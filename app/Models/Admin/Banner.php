<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banner extends Model
{
     use HasFactory;

   protected $table = 'banners';


    protected $fillable = [
        'title',
        'description',
        'image',
        'sequence',
        'status',
        'created_by',
        'updated_by',
    ];
}
