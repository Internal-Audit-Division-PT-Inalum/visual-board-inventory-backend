<?php

namespace App\Filament\Resources\Zones\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Schema;

class ZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Zona')
                    ->required(),
                TextInput::make('area')
                    ->label('Area')
                    ->required(),
                Select::make('pic_utama_id')
                    ->label('PIC Utama')
                    ->relationship('picUtama', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('pic_pengganti_id')
                    ->label('PIC Pengganti')
                    ->relationship('picPengganti', 'name')
                    ->searchable()
                    ->preload(),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->required(),
                SpatieMediaLibraryFileUpload::make('standard_images')
                    ->label('Foto Standar Ruangan')
                    ->collection('standard_images')
                    ->image()
                    ->imageEditor()
                    ->multiple()
                    ->columnSpanFull(),
            ]);
    }
}
