<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Akun Pengguna (Opsional)')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('department_id')
                    ->label('Departemen')
                    ->relationship('department', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('namecode')
                    ->label('Kode/Nama Karyawan')
                    ->required()
                    ->maxLength(255),
                TextInput::make('position_title')
                    ->label('Jabatan')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
