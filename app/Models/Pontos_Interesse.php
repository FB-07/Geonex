<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pontos_Interesse extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'pontos_interesse';

    protected $fillable = [
        'gfuser_id',
        'nome',
        'color',
        'distrito_id',
        'municipio_id',
        'freguesia_id',
    ];

    public function places(): BelongsToMany
    {
        return $this->belongsToMany(
            Place::class,
            'ponto_interesse_place',
            'ponto_interesse_id',
            'place_id',
            'id',
            'place_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gfuser_id', 'gfuser_id');
    }
}