<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE inventory_ledgers DROP CONSTRAINT IF EXISTS inventory_ledgers_type_check');
            DB::statement("ALTER TABLE inventory_ledgers ADD CONSTRAINT inventory_ledgers_type_check CHECK (type::text = ANY (ARRAY['in'::character varying, 'out'::character varying, 'borrow'::character varying, 'return'::character varying, 'adjustment'::character varying]::text[]))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE inventory_ledgers DROP CONSTRAINT IF EXISTS inventory_ledgers_type_check');
            DB::statement("ALTER TABLE inventory_ledgers ADD CONSTRAINT inventory_ledgers_type_check CHECK (type::text = ANY (ARRAY['in'::character varying, 'out'::character varying, 'borrow'::character varying, 'return'::character varying]::text[]))");
        }
    }
};
