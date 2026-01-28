<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distrito extends Model
{
    protected $connection = 'mysql2';
    protected $fillable = ['nome'];

    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class);
    }
}

