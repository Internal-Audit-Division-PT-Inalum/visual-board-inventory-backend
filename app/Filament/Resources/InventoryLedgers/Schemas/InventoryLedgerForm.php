<?php

namespace App\Filament\Resources\InventoryLedgers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventoryLedgerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('item_id')
                    ->label('Barang')
                    ->relationship('item', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->label('Pelaku (User)')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('type')
                    ->label('Jenis Transaksi')
                    ->options([
                        'in' => 'Barang Masuk (In)',
                        'out' => 'Barang Keluar (Out)',
                        'adjustment' => 'Penyesuaian (Adjustment)',
                    ])
                    ->required(),
                TextInput::make('quantity')
                    ->label('Kuantitas')
                    ->required()
                    ->numeric(),
                TextInput::make('stock_before')
                    ->label('Stok Sebelum')
                    ->required()
                    ->numeric(),
                TextInput::make('stock_after')
                    ->label('Stok Sesudah')
                    ->required()
                    ->numeric(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
                TextInput::make('reference_number')
                    ->label('Nomor Referensi'),
            ]);
    }
}
