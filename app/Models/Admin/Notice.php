<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = [
        'title',
        'description',
        'href',
        'button_text',
        'status',
        'created_by',
        'updated_by',
    ];
}
