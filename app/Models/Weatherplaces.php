<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weatherplaces extends Model
{
    use HasFactory;

    protected $table = 'weatherplaces'; 

    protected $primaryKey = 'weatherplaces_id';

    public $timestamps = false;
    protected $fillable = [
        'weatherplaces_owner',
        'weatherplaces_country',
        'weatherplaces_idRegiao',
        'weatherplaces_idAreaAviso',
        'weatherplaces_idConcelho',
        'weatherplaces_globalIdLocal',
        'weatherplaces_latitude',
        'weatherplaces_idDistrito',
        'weatherplaces_longitude',
    ];


}

