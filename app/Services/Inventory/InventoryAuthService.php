<?php

namespace App\Services\Inventory;

use App\Domains\HR\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class InventoryAuthService
{
    const MAX_ATTEMPTS = 5;

    const LOCKOUT_MINUTES = 15;

    const WEAK_PINS = ['000000', '123456', '111111', '654321', '123123'];

    public function login(string $namecode, string $pin): array
    {
        $employee = Employee::where('namecode', strtoupper($namecode))
            ->where('is_active', true)
            ->first();

        if (! $employee || ! $employee->user) {
            $this->throwGenericError();
        }

        if ($employee->pin_locked_until && $employee->pin_locked_until->isFuture()) {
            throw ValidationException::withMessages([
                'namecode' => ['Akun terkunci sementara. Silakan coba lagi nanti.'],
            ]);
        }

        if (! $employee->pin || ! Hash::check($pin, $employee->pin)) {
            $this->incrementFailedAttempts($employee);
            $this->throwGenericError();
        }

        if ($employee->pin_failed_attempts > 0) {
            $employee->update([
                'pin_failed_attempts' => 0,
                'pin_locked_until' => null,
            ]);
        }

        $mustChangePin = $employee->must_change_pin || $this->isWeakPin($pin);

        $token = $employee->user->createToken(
            'inventory-pwa',
            ['inventory'],
            now()->addDays(30)
        );

        return [
            'token' => $token->plainTextToken,
            'must_change_pin' => $mustChangePin,
            'employee' => $employee,
        ];
    }

    public function changePin(Employee $employee, string $currentPin, string $newPin): void
    {
        if (! $employee->pin || ! Hash::check($currentPin, $employee->pin)) {
            throw ValidationException::withMessages([
                'current_pin' => ['PIN saat ini tidak cocok.'],
            ]);
        }

        if ($this->isWeakPin($newPin)) {
            throw ValidationException::withMessages([
                'new_pin' => ['PIN terlalu mudah ditebak.'],
            ]);
        }

        $employee->update([
            'pin' => Hash::make($newPin),
            'pin_set_at' => now(),
            'must_change_pin' => false,
            'pin_failed_attempts' => 0,
            'pin_locked_until' => null,
        ]);
    }

    private function incrementFailedAttempts(Employee $employee): void
    {
        $attempts = $employee->pin_failed_attempts + 1;
        $updates = ['pin_failed_attempts' => $attempts];

        if ($attempts >= self::MAX_ATTEMPTS) {
            $updates['pin_locked_until'] = now()->addMinutes(self::LOCKOUT_MINUTES);
        }

        $employee->update($updates);
    }

    private function throwGenericError(): void
    {
        // Hash dummy to prevent timing attacks
        Hash::check('dummy', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

        throw ValidationException::withMessages([
            'namecode' => ['Namecode atau PIN salah.'],
        ]);
    }

    private function isWeakPin(string $pin): bool
    {
        if (preg_match('/^(.)\1{5}$/', $pin)) {
            return true;
        }

        return in_array($pin, self::WEAK_PINS);
    }
}
