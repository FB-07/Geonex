<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PontosInteressePlace extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'pontos_interesse_places';

    protected $fillable = [
        'ponto_interesse_id',
        'place_id',
    ];
}
