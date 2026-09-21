<?php

namespace App\Models\Api;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory;


    protected $fillable = [
        'sortname',
        'name',
        'status',
        'phonecode',
        'added_by',
        'updated_by',
    ];

    public function districts()
    {
        return $this->hasMany(District::class, 'country_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Relationship to the user who updated the country
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
