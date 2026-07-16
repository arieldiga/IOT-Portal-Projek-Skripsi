<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gunakan koneksi pgsql_sensor agar tabel dibuat
     * di koneksi yang sama dengan sensor_data.
     */
    protected $connection = 'pgsql_sensor';

    public function up(): void
    {
        Schema::connection('pgsql_sensor')->create('sensor_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql_sensor')->dropIfExists('sensor_users');
    }
};