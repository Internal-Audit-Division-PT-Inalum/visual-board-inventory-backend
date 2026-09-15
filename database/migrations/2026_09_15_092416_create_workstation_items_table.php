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
        Schema::create('workstation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('workstation_id')->constrained('workstations')->cascadeOnDelete();
            $table->foreignUlid('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('standard_quantity')->default(1);
            $table->integer('actual_quantity')->nullable(); // For inspection records
            $table->timestamps();

            $table->unique(['workstation_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workstation_items');
    }
};
