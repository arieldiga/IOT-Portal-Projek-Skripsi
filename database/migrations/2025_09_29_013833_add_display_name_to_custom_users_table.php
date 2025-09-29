<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('custom_users', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('username');
        });
    }

    public function down()
    {
        Schema::table('custom_users', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });
    }
};