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
        Schema::create('master_workstation_criterias', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('item_group');
            $table->string('criteria_code');
            $table->text('standard_criteria');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_workstation_criterias');
    }
};
