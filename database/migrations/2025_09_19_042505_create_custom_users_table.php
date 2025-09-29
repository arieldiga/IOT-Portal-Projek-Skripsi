<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('custom_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('ID - API');
            $table->string('password'); // hashed password
            $table->enum('role', ['super_admin', 'editor', 'viewer']); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_users');
    }
};
