<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->collection('avatar')
                    ->label('Foto Profil (Opsional)')
                    ->image()
                    ->avatar()
                    ->columnSpanFull(),
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
                TextInput::make('employee_code')
                    ->label('Kode Pegawai')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Kode unik pegawai, contoh: K-12345'),
                TextInput::make('name')
                    ->label('Nama Pegawai')
                    ->required()
                    ->maxLength(255),
                TextInput::make('position_title')
                    ->label('Jabatan')
                    ->maxLength(255),
                Select::make('hierarchy_level')
                    ->label('Level Jabatan (Hierarki)')
                    ->options([
                        1 => 'Level 1 – Kepala Divisi',
                        2 => 'Level 2 – Kepala Departemen',
                        3 => 'Level 3 – Senior Auditor',
                        4 => 'Level 4 – Auditor',
                        5 => 'Level 5 – Administrasi',
                    ])
                    ->default(4)
                    ->required()
                    ->helperText('Angka lebih kecil berarti posisi lebih tinggi di struktur organisasi (tampil paling atas).'),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
