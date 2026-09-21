<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ignore in SQLite (used for testing) because SQLite doesn't support DROP CONSTRAINT
        if (DB::getDriverName() !== 'sqlite') {
            // Drop constraint from Laravel enum
            DB::statement('ALTER TABLE general_documents DROP CONSTRAINT IF EXISTS general_documents_category_check');

            // Add new constraint accommodating organization document categories
            DB::statement("ALTER TABLE general_documents ADD CONSTRAINT general_documents_category_check CHECK (category::text = ANY (ARRAY['basic_rule'::character varying, 'flow_process'::character varying, 'kaizen_report'::character varying, 'structure'::character varying, 'map_area'::character varying]::text[]))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE general_documents DROP CONSTRAINT IF EXISTS general_documents_category_check');
            DB::statement("ALTER TABLE general_documents ADD CONSTRAINT general_documents_category_check CHECK (category::text = ANY (ARRAY['basic_rule'::character varying, 'flow_process'::character varying, 'kaizen_report'::character varying]::text[]))");
        }
    }
};
