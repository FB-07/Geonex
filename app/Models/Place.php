<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $connection = 'mysql2';

    protected $fillable = [
        'place_id',
        'name',
        'formatted_address',
        'lat',
        'lng',
        'types',
    ];

    protected $casts = [
        'types' => 'array',
        'lat'   => 'float',
        'lng'   => 'float',
    ];
}
