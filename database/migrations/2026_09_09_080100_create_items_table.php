<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->enum('type', ['consumable', 'asset']);
            $table->string('unit');
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('current_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
