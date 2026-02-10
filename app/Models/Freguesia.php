<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Freguesia extends Model
{
    protected $connection = 'mysql2';
    protected $fillable = ['nome', 'municipio_id'];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function codigosPostais()
    {
        return $this->hasMany(CodigoPostal::class);
    }

}
