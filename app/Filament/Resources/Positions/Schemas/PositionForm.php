<?php

namespace App\Filament\Resources\Positions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Jabatan')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100)
                    ->helperText('Contoh: Dewan Direksi, Kepala Divisi, Staff, dll.'),

                Select::make('hierarchy_level')
                    ->label('Level Hierarki (1 - 7)')
                    ->options([
                        1 => 'Level 1 – Dewan Direksi / Dewan Komisaris',
                        2 => 'Level 2 – Kepala Grup',
                        3 => 'Level 3 – Kepala Divisi',
                        4 => 'Level 4 – Kepala Departemen',
                        5 => 'Level 5 – Kepala Seksi',
                        6 => 'Level 6 – Staff',
                        7 => 'Level 7 – Operator / Administrasi',
                    ])
                    ->required()
                    ->default(6)
                    ->helperText('Angka lebih kecil menunjukkan posisi lebih tinggi dalam struktur organisasi (1 tertinggi, 7 terbawah).'),

                Textarea::make('description')
                    ->label('Deskripsi / Peran')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Penjelasan singkat fungsi dan wewenang jabatan.'),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
