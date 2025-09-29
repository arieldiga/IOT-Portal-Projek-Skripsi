<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sensor_column_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID dari custom_users
            $table->string('column_name'); // nama kolom asli (suhu_1, ph, dll)
            $table->string('custom_label'); // label custom dari admin
            $table->boolean('is_visible')->default(true); // tampilkan atau sembunyikan
            $table->integer('display_order')->default(0); // urutan tampilan
            $table->timestamps();
            
            $table->unique(['user_id', 'column_name']);
            $table->foreign('user_id')->references('id')->on('custom_users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sensor_column_configs');
    }
};