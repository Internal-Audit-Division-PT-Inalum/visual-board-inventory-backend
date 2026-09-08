<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_records', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('monthly_schedule_id')->constrained('monthly_schedules')->cascadeOnDelete();
            $table->foreignUlid('inspection_criteria_id')->constrained('inspection_criterias')->cascadeOnDelete();
            $table->jsonb('days_data')->default('{}');
            $table->timestamps();
            $table->unique(['monthly_schedule_id', 'inspection_criteria_id'], 'schedule_criteria_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_records');
    }
};
