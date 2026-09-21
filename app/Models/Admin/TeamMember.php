<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $table = 'team_members';

    protected $fillable = [
        'name',
        'designation',
        'image',
        'whatsapp',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'integer',
    ];

    /**
     * Full public URL of the member photo, so the storefront never builds paths.
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? url('/team/' . $this->image) : null;
    }
}
