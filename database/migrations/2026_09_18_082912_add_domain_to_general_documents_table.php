<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah kolom 'domain' sebagai discriminator untuk menyatukan
     * general_documents (visual_board) dan organization_documents (organization)
     * ke dalam satu tabel tunggal.
     *
     * 'visual_board' → Dokumen Referensi 5R (Basic Rule, Flow Process, Kaizen Report)
     * 'organization'  → Dokumen Organisasi (Bagan Struktur, Map Area 5R)
     */
    public function up(): void
    {
        Schema::table('general_documents', function (Blueprint $table) {
            $table->string('domain')->default('visual_board')->after('id')
                ->comment('Discriminator: visual_board | organization');
        });
    }

    public function down(): void
    {
        Schema::table('general_documents', function (Blueprint $table) {
            $table->dropColumn('domain');
        });
    }
};
