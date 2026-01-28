<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipio extends Model
{
    protected $connection = 'mysql2';
    protected $fillable = ['nome', 'distrito_id'];

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class);
    }

    public function freguesias(): HasMany
    {
        return $this->hasMany(Freguesia::class);
    }
}
