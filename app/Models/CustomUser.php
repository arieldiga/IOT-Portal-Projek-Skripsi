<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class CustomUser extends Authenticatable
{
    protected $table = 'custom_users';

    protected $fillable = [
        'username',
        'display_name',
        'name',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
