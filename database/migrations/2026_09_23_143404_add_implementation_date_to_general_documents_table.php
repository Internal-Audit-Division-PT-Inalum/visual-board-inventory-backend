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
        Schema::table('general_documents', function (Blueprint $table) {
            $table->unsignedTinyInteger('implementation_month')->nullable()->after('category');
            $table->unsignedSmallInteger('implementation_year')->nullable()->after('implementation_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_documents', function (Blueprint $table) {
            //
        });
    }
};
