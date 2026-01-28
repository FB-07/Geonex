<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XML extends Model
{

    protected $table = 'gfxml'; 
    protected $primaryKey = 'gfxml_id';
    public $timestamps = false;

    protected $fillable = [
        'gfxml_desc',
    ];
}
