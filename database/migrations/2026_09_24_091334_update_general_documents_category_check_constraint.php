<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds self_assessment, asesor, sor, cog, berat_badan categories.
     */
    public function up(): void
    {
        // Drop the existing constraint
        DB::statement('ALTER TABLE general_documents DROP CONSTRAINT IF EXISTS general_documents_category_check');

        // Add updated constraint including new categories
        DB::statement("ALTER TABLE general_documents ADD CONSTRAINT general_documents_category_check CHECK (category::text = ANY (ARRAY[
            'basic_rule'::character varying,
            'flow_process'::character varying,
            'kaizen_report'::character varying,
            'structure'::character varying,
            'map_area'::character varying,
            'sor'::character varying,
            'cog'::character varying,
            'berat_badan'::character varying,
            'self_assessment'::character varying,
            'asesor'::character varying
        ]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE general_documents DROP CONSTRAINT IF EXISTS general_documents_category_check');

        DB::statement("ALTER TABLE general_documents ADD CONSTRAINT general_documents_category_check CHECK (category::text = ANY (ARRAY[
            'basic_rule'::character varying,
            'flow_process'::character varying,
            'kaizen_report'::character varying,
            'structure'::character varying,
            'map_area'::character varying
        ]::text[]))");
    }
};
