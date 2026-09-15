<?php

namespace Database\Seeders;

use App\Domains\Core\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Role Super Admin (Tanpa shield:generate)
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // 1.5. Buat Role Operasional 5R
        $operationalRoles = ['pelaksana_5r', 'staff_penyelia', 'managerial_staff'];
        foreach ($operationalRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Buat Super Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // 3. Assign role to super admin
        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole($role);
        }

        // 4. Dummy Data dengan Environment Guard
        if (app()->environment('local', 'testing', 'staging')) {
            $this->command->warn('Menjalankan Factory Dummy Data untuk Environment: ' . app()->environment());

            $this->call([
                HRSeeder::class,
                InventorySeeder::class,
                PortalSeeder::class,
                VisualBoardSeeder::class,
            ]);
        } else {
            $this->command->info('Environment PRODUCTION terdeteksi. Dummy data dilewati demi keamanan.');
        }
    }
}
