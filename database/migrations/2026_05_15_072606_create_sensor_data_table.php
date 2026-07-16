<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql_sensor';

    public function up(): void
{
    Schema::connection('pgsql_sensor')->dropIfExists('sensor_data'); // ← tambah ini
    Schema::connection('pgsql_sensor')->create('sensor_data', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->timestamp('datetime');
        $table->float('ph')->nullable();
        $table->float('cod')->nullable();
        $table->float('tss')->nullable();

        $table->foreign('user_id')
            ->references('id')
            ->on('sensor_users')
            ->onDelete('cascade');
    });
}

    public function down(): void
    {
        Schema::connection('pgsql_sensor')->dropIfExists('sensor_data');
    }
};