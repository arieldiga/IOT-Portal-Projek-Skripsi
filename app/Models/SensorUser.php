<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorUser extends Model
{
    protected $connection = 'pgsql_sensor'; // pakai koneksi DB sensor
    protected $table = 'users';             // tabel di lsa_sensor
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'username',
    ];
}
