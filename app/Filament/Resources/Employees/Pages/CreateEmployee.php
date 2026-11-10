<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Domains\Core\Models\User;
use App\Domains\HR\Models\Employee;
use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected bool $shouldProvisionPwa = false;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $rawState = $this->form->getRawState();
        $this->shouldProvisionPwa = (bool) ($data['provision_pwa_account'] ?? $rawState['provision_pwa_account'] ?? false);
        unset($data['provision_pwa_account']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->shouldProvisionPwa) {
            /** @var Employee $employee */
            $employee = $this->record;

            if (! $employee->user_id && ! empty($employee->namecode)) {
                $syntheticEmail = strtolower($employee->namecode) . '@synthetic.inalum.id';
                $user = User::firstOrCreate(
                    ['email' => $syntheticEmail],
                    [
                        'name' => $employee->name,
                        'password' => Hash::make(Str::random(16)),
                    ]
                );

                $employee->user_id = $user->id;
                if (! $employee->pin) {
                    $employee->pin = Hash::make('123456');
                    $employee->pin_set_at = now();
                    $employee->must_change_pin = true;
                }
                $employee->saveQuietly();
            }
        }
    }
}
