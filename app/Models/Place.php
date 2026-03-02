<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'color',
        'distrito_id',
        'municipio_id',
        'freguesia_id',
    ];

    protected $casts = [
        'types' => 'array',
        'lat'   => 'float',
        'lng'   => 'float',
    ];

    public function setColorAttribute($value)
    {
        if ($value) {
            $this->attributes['color'] = strtoupper($value);
        }
    }

    public function distrito()
    {
        return $this->belongsTo(Distrito::class);
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function freguesia()
    {
        return $this->belongsTo(Freguesia::class);
    }

    public function pontosInteresse(): BelongsToMany
    {
        return $this->belongsToMany(
            Pontos_Interesse::class,
            'ponto_interesse_place',
            'place_id',
            'ponto_interesse_id',
            'place_id',
            'id'
        );
    }
}
