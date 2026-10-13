<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $primaryKey = 'gfuser_id';

    public $timestamps = false;

    protected $hidden = [
        'gfuser_password',
    ];

    public function getAuthPassword()
    {
        return $this->gfuser_password;
    }
}