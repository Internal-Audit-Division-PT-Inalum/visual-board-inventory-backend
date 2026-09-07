<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_criteria', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->string('item_group');
            $table->string('criteria_code', 10);
            $table->text('description');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_criterias');
    }
};
