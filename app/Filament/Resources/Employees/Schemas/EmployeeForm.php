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
                TextInput::make('namecode')
                    ->label('Kode/Nama Karyawan')
                    ->required()
                    ->maxLength(255),
                TextInput::make('position_title')
                    ->label('Jabatan')
                    ->maxLength(255),
                Select::make('hierarchy_level')
                    ->label('Level Jabatan (Hierarki)')
                    ->options([
                        1 => 'Level 1 (Pucuk Pimpinan / Kepala Divisi)',
                        2 => 'Level 2 (Manajemen Menengah / Kepala Departemen)',
                        3 => 'Level 3 (Pelaksana Utama / Lead Auditor)',
                        4 => 'Level 4 (Pelaksana Lapangan / Auditor)',
                        5 => 'Level 5 (Administratif / Staf Pendukung)',
                    ])
                    ->default(5)
                    ->required()
                    ->helperText('Angka lebih kecil berarti posisi lebih tinggi di struktur organisasi (tampil paling atas).'),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
