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
                    ->label('Namecode / NIK Pegawai')
                    ->required()
                    ->numeric()
                    ->rule('regex:/^[0-9]+$/')
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->helperText('Nomor identitas unik pegawai berupa angka murni PT INALUM (tanpa huruf, contoh: 102934).'),

                TextInput::make('employee_code')
                    ->label('Kode Pegawai')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100)
                    ->helperText('Kode unik pegawai, contoh: 102934 atau EMP-102934'),

                TextInput::make('name')
                    ->label('Nama Lengkap Pegawai')
                    ->required()
                    ->maxLength(255),

                Select::make('position_id')
                    ->label('Jabatan Resmi')
                    ->relationship('position', 'name', fn ($query) => $query->where('is_active', true)->orderBy('hierarchy_level'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih nomenklatur jabatan resmi PT INALUM.'),

                Toggle::make('provision_pwa_account')
                    ->label('Otomatis Buat Akun PWA & Set PIN Default (123456)')
                    ->default(true)
                    ->helperText('Jika dicentang, akun login PWA ({namecode}@synthetic.inalum.id) otomatis dibuat dan siap digunakan.')
                    ->dehydrated(false)
                    ->visibleOn('create'),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
