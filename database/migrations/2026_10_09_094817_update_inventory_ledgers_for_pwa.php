<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop check constraint for enum to allow 'adjustment' (PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE inventory_ledgers DROP CONSTRAINT IF EXISTS inventory_ledgers_type_check');
        }

        Schema::table('inventory_ledgers', function (Blueprint $table) {
            $table->uuid('client_uuid')->nullable()->unique()->after('id');
            $table->timestamp('occurred_at')->nullable()->after('client_uuid');

            // Modify FK constraints
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['item_id']);

                $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->restrictOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_ledgers', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['item_id']);

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            }

            $table->dropColumn('client_uuid');
            $table->dropColumn('occurred_at');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE inventory_ledgers ADD CONSTRAINT inventory_ledgers_type_check CHECK (type::text = ANY (ARRAY['in'::character varying, 'out'::character varying, 'borrow'::character varying, 'return'::character varying]::text[]))");
        }
    }
};
