<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoPostal extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'codigos_postais';

    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'codigo',
        'freguesia_id',
    ];

    public function freguesia()
    {
        return $this->belongsTo(Freguesia::class);
    }
}