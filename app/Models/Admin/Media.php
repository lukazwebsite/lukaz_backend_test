<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'product_additional_gallaries';
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'color_galleries' => 'json',
        'color_galleries_small' => 'json'
    ];
}
