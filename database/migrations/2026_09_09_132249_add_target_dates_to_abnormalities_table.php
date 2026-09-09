<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abnormalities', function (Blueprint $table) {
            $table->date('target_date')->nullable()->after('status');
            $table->date('actual_resolution_date')->nullable()->after('target_date');
        });
    }

    public function down(): void
    {
        Schema::table('abnormalities', function (Blueprint $table) {
            $table->dropColumn(['target_date', 'actual_resolution_date']);
        });
    }
};
