<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    protected $connection = 'pgsql_sensor'; // koneksi DB lsa_sensor
    protected $table = 'sensor_data';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'datetime',
        'ph',
        'cod',
        'tss',
    ];

    public function user()
    {
        return $this->belongsTo(SensorUser::class, 'user_id', 'id');
    }
}
