<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add new separate columns for code and name
            $table->string('employee_code')->nullable()->after('department_id')->comment('Kode unik pegawai, misal: K-12345');
            $table->string('name')->nullable()->after('employee_code')->comment('Nama lengkap pegawai');
        });

        // Migrate existing namecode data: copy to employee_code and name
        // Assumption: namecode may have been used as identifier; copy as-is to employee_code
        // and leave name as null to be filled by admin.
        DB::table('employees')->get()->each(function ($emp) {
            DB::table('employees')->where('id', $emp->id)->update([
                'employee_code' => $emp->namecode,
                'name' => $emp->namecode, // duplicate to name so existing data is preserved
            ]);
        });

        // Make employee_code required and unique after data migration
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_code')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['employee_code']);
            $table->dropColumn(['employee_code', 'name']);
        });
    }
};
