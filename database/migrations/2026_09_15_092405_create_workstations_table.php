<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workstations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->foreignUlid('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workstations');
    }
};
