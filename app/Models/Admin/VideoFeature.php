<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoFeature extends Model
{
    use HasFactory;

    protected $table = 'video_feature';

    // Fillable fields
    protected $fillable = [
        'title',
        'description',
        'video_link',
        'button_text',
        'status',
        'created_by',
        'updated_by'
    ];
}
