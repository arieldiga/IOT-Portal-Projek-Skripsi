<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    protected $connection = 'pgsql_sensor';
    protected $table = 'sensor_data';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'datetime',
        'ph',
        'cod',
        'tss',
        'nh3n',
        'debit',
        'conductivity',
        'suhu',
        'orp',
        'tds',
        'turbidity',
        'corrosion_rate',
        'corrosion_inhibitor',
        'scale_inhibitor',
        'lvl_biocid_p',
        'lvl_naoh_p',
        'lvl_non_ox_bioa_p',
        'lvl_non_ox_biob_p',
        'suhu_1',
        'suhu_2',
        'suhu_3',
        'suhu_4',
        'suhu_5',
        'suhu_6',
        'suhu_7',
        'suhu_8',
    ];

    public function user()
    {
        return $this->belongsTo(SensorUser::class, 'user_id', 'id');
    }
}