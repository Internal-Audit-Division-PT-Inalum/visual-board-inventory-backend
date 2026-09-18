<?php

namespace App\Filament\Resources\TrendAbnormalities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TrendAbnormalityForm
{
    public static function configure(Schema $schema): Schema
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $schema
            ->components([
                TextInput::make('year')
                    ->label('Tahun')
                    ->numeric()
                    ->required()
                    ->minValue(2020)
                    ->maxValue(2100)
                    ->default(now()->year),
                Select::make('month')
                    ->label('Bulan')
                    ->options($months)
                    ->required()
                    ->default(now()->month),
                Select::make('zone_label')
                    ->label('Zona')
                    ->options([
                        'Z-1' => 'Z-1 (Zona 1)',
                        'Z-2' => 'Z-2 (Zona 2)',
                        'Z-3' => 'Z-3 (Zona 3)',
                    ])
                    ->required(),
                TextInput::make('temuan')
                    ->label('Temuan Baru (X)')
                    ->helperText('Jumlah temuan baru pada bulan ini')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('tindak_lanjut')
                    ->label('Tindak Lanjut / Selesai (O)')
                    ->helperText('Jumlah temuan yang berhasil diselesaikan')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('belum_selesai')
                    ->label('Belum Selesai (Δ)')
                    ->helperText('Sisa temuan yang belum diselesaikan')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
            ]);
    }
}
