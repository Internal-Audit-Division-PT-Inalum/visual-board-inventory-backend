<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Domains\Core\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('namecode')
                    ->label('Namecode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee_code')
                    ->label('Kode Pegawai')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Departemen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position_title')
                    ->label('Jabatan')
                    ->searchable(),
                TextColumn::make('hierarchy_level')
                    ->label('Level')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'success',
                        4 => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('user.name')
                    ->label('Akun Pengguna')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('pin_status')
                    ->label('Status PIN')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->pin_locked_until && $record->pin_locked_until->isFuture()) {
                            return 'Terkunci';
                        }

                        return $record->pin ? 'Set' : 'Belum Set';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Terkunci' => 'danger',
                        'Set' => 'success',
                        default => 'warning',
                    }),
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),

                    Action::make('reset_pin')
                        ->label('Set/Reset PIN')
                        ->icon('heroicon-o-key')
                        ->form([
                            TextInput::make('new_pin')
                                ->label('PIN Baru')
                                ->password()
                                ->required()
                                ->length(6)
                                ->numeric(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'pin' => Hash::make($data['new_pin']),
                                'pin_set_at' => now(),
                                'must_change_pin' => true,
                                'pin_failed_attempts' => 0,
                                'pin_locked_until' => null,
                            ]);
                            Notification::make()->title('PIN berhasil direset.')->success()->send();
                        }),

                    Action::make('unlock')
                        ->label('Buka Kunci PIN')
                        ->icon('heroicon-o-lock-open')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->pin_locked_until && $record->pin_locked_until->isFuture())
                        ->action(function ($record) {
                            $record->update([
                                'pin_failed_attempts' => 0,
                                'pin_locked_until' => null,
                            ]);
                            Notification::make()->title('Kunci PIN berhasil dibuka.')->success()->send();
                        }),

                    Action::make('revoke_sessions')
                        ->label('Cabut Sesi PWA')
                        ->icon('heroicon-o-arrow-right-on-rectangle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->user !== null)
                        ->action(function ($record) {
                            $record->user->tokens()->delete();
                            Notification::make()->title('Semua sesi PWA berhasil dicabut.')->success()->send();
                        }),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('provision_users')
                        ->label('Provision PWA Accounts')
                        ->icon('heroicon-o-user-plus')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records->whereNull('user_id') as $employee) {
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
                            Notification::make()->title("$count akun berhasil di-provision.")->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
