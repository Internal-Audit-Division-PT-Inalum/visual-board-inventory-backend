<?php

namespace App\Filament\Resources\Users\Pages;

use App\Domains\Core\Models\User;
use App\Filament\Resources\Users\UserResource;
use DomainException;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @var array<int, string>
     */
    protected array $rolesBeforeSave = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn (User $record): bool => $record->id === auth()->id() || $record->isLastStandingSuperAdmin()),
        ];
    }

    protected function beforeValidate(): void
    {
        /** @var User $record */
        $record = $this->getRecord();
        $this->rolesBeforeSave = $record->roles()->pluck('name')->values()->all();

        if ($record->id === auth()->id()) {
            $this->data['roles'] = $record->roles()->pluck('id')->map(fn ($id): string => (string) $id)->all();
        }
    }

    protected function beforeSave(): void
    {
        /** @var User $record */
        $record = $this->getRecord();

        if ($record->id === auth()->id()) {
            return;
        }

        if ($record->isLastStandingSuperAdmin()) {
            $selectedRoleIds = array_map('strval', (array) ($this->data['roles'] ?? []));
            $superAdminRoleId = (string) Role::where('name', 'super_admin')->value('id');

            if (! in_array($superAdminRoleId, $selectedRoleIds, true)) {
                Notification::make()
                    ->title('Operasi ditolak. Sistem wajib memiliki minimal satu akun Super Admin aktif.')
                    ->danger()
                    ->send();

                throw new DomainException('Operasi ditolak. Sistem wajib memiliki minimal satu akun Super Admin aktif.');
            }
        }
    }

    protected function afterSave(): void
    {
        /** @var User $record */
        $record = $this->getRecord();
        $rolesAfterSave = $record->roles()->pluck('name')->values()->all();

        if ($this->rolesBeforeSave !== $rolesAfterSave) {
            Log::info('Security Audit: User roles modified', [
                'event' => 'user.roles_updated',
                'actor_id' => auth()->id(),
                'actor_email' => auth()->user()?->email,
                'target_user_id' => $record->id,
                'target_user_email' => $record->email,
                'roles_before' => $this->rolesBeforeSave,
                'roles_after' => $rolesAfterSave,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }
}
