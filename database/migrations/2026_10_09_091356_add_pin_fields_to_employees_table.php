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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('pin')->nullable()->after('is_active');
            $table->timestamp('pin_set_at')->nullable()->after('pin');
            $table->boolean('must_change_pin')->default(false)->after('pin_set_at');
            $table->unsignedInteger('pin_failed_attempts')->default(0)->after('must_change_pin');
            $table->timestamp('pin_locked_until')->nullable()->after('pin_failed_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'pin',
                'pin_set_at',
                'must_change_pin',
                'pin_failed_attempts',
                'pin_locked_until',
            ]);
        });
    }
};
