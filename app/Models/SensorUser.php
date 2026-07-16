<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorUser extends Model
{
    protected $connection = 'pgsql_sensor';
    protected $table      = 'sensor_users';   // ← diganti dari 'users' agar tidak bentrok
    protected $primaryKey = 'id';
    public $timestamps    = true;

    protected $fillable = [
        'id',
        'username',
    ];
}