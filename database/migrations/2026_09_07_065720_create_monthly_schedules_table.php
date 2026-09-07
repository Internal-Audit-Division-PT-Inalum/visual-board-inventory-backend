<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_schedules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->date('period_month');
            $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft');
            $table->jsonb('approval_data')->default('{}');
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['zone_id', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_schedules');
    }
};
