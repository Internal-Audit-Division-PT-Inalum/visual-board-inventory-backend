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
        Schema::create('five_r_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('month');
            $table->integer('year');
            $table->enum('type', ['self_assessment', 'asesor']);
            $table->decimal('total_score', 4, 2); // e.g. 5.00
            $table->string('evaluation_file')->nullable();
            $table->timestamps();

            // Ensure only one evaluation per month/year per type
            $table->unique(['month', 'year', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('five_r_evaluations');
    }
};
