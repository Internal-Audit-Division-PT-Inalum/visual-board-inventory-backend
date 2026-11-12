<?php

namespace Database\Seeders;

use App\Domains\Core\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RbacSeeder::class);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole($role);
        }

        $this->call(PositionSeeder::class);
    }
}
