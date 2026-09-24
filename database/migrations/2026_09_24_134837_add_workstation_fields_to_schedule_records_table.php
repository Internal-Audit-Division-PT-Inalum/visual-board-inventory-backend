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
        Schema::table('schedule_records', function (Blueprint $table) {
            $table->foreignUlid('workstation_id')->nullable()->constrained('workstations')->cascadeOnDelete();
            $table->foreignUlid('master_workstation_criteria_id')->nullable()->constrained('master_workstation_criterias')->cascadeOnDelete();
            $table->foreignUlid('inspection_criteria_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_records', function (Blueprint $table) {
            $table->dropForeign(['workstation_id']);
            $table->dropForeign(['master_workstation_criteria_id']);
            $table->dropColumn(['workstation_id', 'master_workstation_criteria_id']);
        });
    }
};
