<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropForeign(['pic_utama_id']);
            $table->dropForeign(['pic_pengganti_id']);

            $table->foreign('pic_utama_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('pic_pengganti_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropForeign(['pic_utama_id']);
            $table->dropForeign(['pic_pengganti_id']);

            $table->foreign('pic_utama_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('pic_pengganti_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
