<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('location_id')
                    ->label('Lokasi')
                    ->relationship('location', 'name')
                    ->required(),
                TextInput::make('name')
                    ->label('Nama Barang')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'consumable' => 'Habis Pakai (Consumable)',
                        'asset' => 'Aset (Asset)',
                    ])
                    ->required(),
                TextInput::make('unit')
                    ->label('Satuan')
                    ->required(),
                TextInput::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->required()
                    ->numeric()
                    ->default(0),
                SpatieMediaLibraryFileUpload::make('image')
                    ->label('Gambar Barang')
                    ->collection('item_images')
                    ->image()
                    ->maxSize(2048)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->required(),
            ]);
    }
}
