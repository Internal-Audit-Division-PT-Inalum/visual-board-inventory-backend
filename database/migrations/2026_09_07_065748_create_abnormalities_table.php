<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abnormalities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->foreignUlid('monthly_schedule_id')->nullable()->constrained('monthly_schedules')->nullOnDelete();
            $table->foreignUlid('inspection_criteria_id')->nullable()->constrained('inspection_criteria')->nullOnDelete();

            $table->date('date_found');
            $table->text('problem_description');
            $table->text('countermeasure_plan')->nullable();
            $table->text('countermeasure_actual')->nullable();

            $table->foreignUlid('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open');
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->boolean('is_kaizen')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abnormalities');
    }
};
