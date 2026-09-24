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
        Schema::disableForeignKeyConstraints();
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('parent_id');
            $table->string('division')->default('IIA (Inalum Internal Audit)')->after('name');
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('division');
            $table->string('parent_id')->nullable();
        });
    }
};
