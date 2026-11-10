<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->unique()->comment('Nama Jabatan Baku INALUM');
            $table->unsignedTinyInteger('hierarchy_level')->comment('Level 1 s/d 7');
            $table->text('description')->nullable()->comment('Deskripsi peran fungsional');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hierarchy_level', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
