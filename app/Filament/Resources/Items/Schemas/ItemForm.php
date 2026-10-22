<?php

namespace App\Filament\Resources\Items\Schemas;

use App\Domains\Inventory\Models\Item;
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
                    ->required()
                    ->disabled(fn (?Item $record): bool => $record !== null && $record->ledgers()->exists())
                    ->helperText(fn (?Item $record): ?string => ($record !== null && $record->ledgers()->exists()) ? 'Tipe barang dikunci karena sudah memiliki riwayat transaksi/ledger.' : null)
                    ->dehydrated(fn (?Item $record): bool => ! ($record !== null && $record->ledgers()->exists())),
                TextInput::make('unit')
                    ->label('Satuan')
                    ->required(),
                TextInput::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Stok tidak dapat diubah langsung saat edit barang demi menjaga audit trail ledger. Gunakan aksi "Koreksi Stok" di tabel barang.' : 'Stok awal barang saat pertama kali didaftarkan.')
                    ->dehydrated(fn (string $operation): bool => $operation !== 'edit'),
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
