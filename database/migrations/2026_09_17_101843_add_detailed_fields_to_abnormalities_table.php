<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abnormalities', function (Blueprint $table) {
            // Kolom penemu (dari form dokumen: "Penemu" - bisa text atau user)
            $table->string('finder_name')->nullable()->after('date_found');
            // Kolom grup (dari form: "Grup")
            $table->string('group_name')->nullable()->after('finder_name');
            // Tanggal penanggulangan terencana & aktual (rename dari target_date → planned_date)
            // kita tambah alias tanpa hapus field lama agar backward compat
            $table->date('planned_date')->nullable()->after('countermeasure_plan');
            $table->date('actual_date')->nullable()->after('planned_date');
            // Verifikasi tanda tangan (string nama, bukan FK, untuk kemudahan input)
            $table->string('signed_by_staff')->nullable()->after('is_kaizen');
            $table->string('signed_by_ms')->nullable()->after('signed_by_staff');
        });
    }

    public function down(): void
    {
        Schema::table('abnormalities', function (Blueprint $table) {
            $table->dropColumn([
                'finder_name',
                'group_name',
                'planned_date',
                'actual_date',
                'signed_by_staff',
                'signed_by_ms',
            ]);
        });
    }
};
