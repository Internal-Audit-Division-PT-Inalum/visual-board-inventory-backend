<?php

namespace App\Console\Commands;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InventoryProvisionUsers extends Command
{
    protected $signature = 'inventory:provision-users';

    protected $description = 'Provision synthetic users for employees without users';

    public function handle()
    {
        $this->info('Provisioning users for employees...');

        $employees = Employee::whereNull('user_id')->get();
        $count = 0;

        foreach ($employees as $employee) {
            $syntheticEmail = strtolower($employee->namecode) . '@synthetic.inalum.id';

            $user = User::firstOrCreate(
                ['email' => $syntheticEmail],
                [
                    'name' => $employee->name ?? $employee->namecode,
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            $employee->update(['user_id' => $user->id]);
            $count++;
        }

        $this->info("Provisioned $count users successfully.");
    }
}
