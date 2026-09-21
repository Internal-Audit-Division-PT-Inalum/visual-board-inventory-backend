<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abnormalities', function (Blueprint $table) {
            $table->foreignUlid('verified_by_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at_staff')->nullable();
            $table->foreignUlid('verified_by_ms_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at_ms')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('abnormalities', function (Blueprint $table) {
            $table->dropForeign(['verified_by_staff_id']);
            $table->dropForeign(['verified_by_ms_id']);
            $table->dropColumn([
                'verified_by_staff_id',
                'verified_at_staff',
                'verified_by_ms_id',
                'verified_at_ms',
            ]);
        });
    }
};
