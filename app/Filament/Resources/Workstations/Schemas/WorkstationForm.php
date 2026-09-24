<?php

namespace App\Filament\Resources\Workstations\Schemas;

use Filament\Forms\Components;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkstationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Nama Meja / Workstation')
                            ->required()
                            ->maxLength(255),
                        Components\Select::make('zone_id')
                            ->label('Zona')
                            ->relationship('zone', 'name')
                            ->required()
                            ->searchable(),
                        Components\Select::make('employee_id')
                            ->label('Pegawai (PIC Meja)')
                            ->relationship(
                                name: 'employee',
                                titleAttribute: 'name',
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->namecode})")
                            ->searchable(['name', 'namecode'])
                            ->nullable(),
                        Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),
                    ])->columns(2),

                Section::make('Standar Visual 5R (Standard Image)')
                    ->schema([
                        Components\SpatieMediaLibraryFileUpload::make('standard_images')
                            ->collection('standard_images')
                            ->label('Foto Standar Meja (Tampak Rapi)')
                            ->image()
                            ->maxSize(5120)
                            ->helperText('Unggah foto yang menunjukkan kondisi ideal (standar 5R) untuk meja ini.'),
                    ]),
            ]);
    }
}
