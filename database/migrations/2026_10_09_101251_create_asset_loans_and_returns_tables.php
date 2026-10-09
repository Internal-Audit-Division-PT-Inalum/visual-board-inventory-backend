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
        Schema::create('asset_loans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('item_id')->constrained('items')->restrictOnDelete();
            $table->foreignUlid('employee_id')->constrained('employees')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('returned_quantity')->default(0);
            $table->timestamp('borrowed_at');
            $table->timestamp('fully_returned_at')->nullable();
            $table->foreignUlid('borrow_ledger_id')->constrained('inventory_ledgers')->restrictOnDelete();
            $table->timestamps();

            // Indeks: (employee_id, item_id, fully_returned_at)
            $table->index(['employee_id', 'item_id', 'fully_returned_at']);
        });

        Schema::create('asset_loan_returns', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('asset_loan_id')->constrained('asset_loans')->restrictOnDelete();
            $table->foreignUlid('ledger_id')->constrained('inventory_ledgers')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamp('returned_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_loan_returns');
        Schema::dropIfExists('asset_loans');
    }
};
