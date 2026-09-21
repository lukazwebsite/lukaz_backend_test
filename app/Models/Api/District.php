<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'courier_charge',
        'name',
        'bn_name',
        'lat',
        'lon',
        'website',
    ];

    /**
     * Relationship: District belongs to a country
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
