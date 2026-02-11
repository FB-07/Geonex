<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $connection = 'mysql2';

    protected $table = 'users';

    protected $primaryKey = 'gfuser_id';

    public $timestamps = false;

    protected $rememberTokenName = null;

    protected $fillable = [
        'gfuser_alias',
        'gfuser_password',
        'gfuser_fullname',
        'gfuser_sex',
        'gfuser_active',
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
