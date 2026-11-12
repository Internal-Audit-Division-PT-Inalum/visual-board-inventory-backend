<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Domains\Core\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),

                Select::make('roles')
                    ->label('Peran (Role)')
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => str($record->name)->replace('_', ' ')->title())
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->disabled(fn (?User $record): bool => $record !== null && $record->id === auth()->id())
                    ->helperText(fn (?User $record): string => $record !== null && $record->id === auth()->id()
                        ? 'Anda tidak dapat mengubah peran akun Anda sendiri untuk alasan kepatuhan keamanan.'
                        : 'Pilih peran operasional pengguna.')
                    ->saveRelationshipsUsing(function (Select $component, ?User $record, $state): void {
                        if ($record === null) {
                            return;
                        }

                        if ($record->id === auth()->id()) {
                            return;
                        }

                        $roleIds = array_map('intval', Arr::wrap($state ?? []));
                        $record->syncRoles($roleIds);
                    }),

                DateTimePicker::make('email_verified_at')
                    ->label('Verifikasi Email (Bypass)')
                    ->helperText('Isi dengan waktu saat ini jika ingin melewati proses verifikasi email wajib.'),
            ]);
    }
}
