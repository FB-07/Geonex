<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pontos_Interesse extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';
    protected $table = 'pontos_interesse';
    protected $guarded = [];
}
