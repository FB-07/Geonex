<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $primaryKey = 'gfuser_id';

    public $timestamps = false;

    protected $fillable = [
        'gfuser_active',
        'gfuser_alias',
        'gfuser_fullname',
        'gfuser_password',
        'gfuser_sex',
        'gfuser_email',
    ];

    protected $hidden = [
        'gfuser_password',
    ];

    public function getAuthPassword()
    {
        return $this->gfuser_password;
    }
}