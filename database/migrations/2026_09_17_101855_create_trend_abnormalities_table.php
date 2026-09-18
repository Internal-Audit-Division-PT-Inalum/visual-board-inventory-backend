<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trend_abnormalities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedSmallInteger('year');
            // Bulan 1-12
            $table->unsignedTinyInteger('month');
            // Nama zona: Z-1, Z-2, Z-3 atau relasi zona
            $table->string('zone_label'); // e.g. "Z-1", "Z-2", "Z-3"
            // X = Temuan baru pada bulan ini
            $table->unsignedSmallInteger('temuan')->default(0);
            // O = Temuan yang berhasil diselesaikan pada bulan ini
            $table->unsignedSmallInteger('tindak_lanjut')->default(0);
            // Δ = Sisa temuan yang belum selesai (dihitung atau diinput manual)
            $table->unsignedSmallInteger('belum_selesai')->default(0);
            $table->timestamps();

            // Pastikan 1 zona hanya punya 1 record per bulan per tahun
            $table->unique(['year', 'month', 'zone_label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trend_abnormalities');
    }
};
