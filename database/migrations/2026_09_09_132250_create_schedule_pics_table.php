<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_pics', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('monthly_schedule_id')->constrained('monthly_schedules')->cascadeOnDelete();
            $table->date('date');
            $table->foreignUlid('pic_id')->constrained('users')->cascadeOnDelete();
            $table->enum('pic_role', ['utama', 'pengganti'])->default('utama');
            $table->timestamps();

            // A PIC can only have one role per day per schedule
            $table->unique(['monthly_schedule_id', 'date', 'pic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_pics');
    }
};
