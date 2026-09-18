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
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedTinyInteger('hierarchy_level')->default(5)->after('position_title')->comment('1 = Kepala Divisi, 2 = Kepala Departemen, 3 = Lead Auditor, 4 = Auditor, 5 = Admin/Staf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('hierarchy_level');
        });
    }
};
