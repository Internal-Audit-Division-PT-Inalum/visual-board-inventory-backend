<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menghapus constraint bawaan PostgreSQL agar kita bisa menambah tipe baru
        DB::statement('ALTER TABLE bulletins DROP CONSTRAINT IF EXISTS bulletins_type_check');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Memulihkan constraint jika di-rollback
        DB::statement("ALTER TABLE bulletins ADD CONSTRAINT bulletins_type_check CHECK (type::text = ANY (ARRAY['general'::character varying, 'health_safety'::character varying]::text[]))");
    }
};
