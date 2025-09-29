<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorColumnConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'column_name',
        'custom_label',
        'is_visible',
        'display_order'
    ];

    protected $casts = [
        'is_visible' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(CustomUser::class, 'user_id');
    }
}