<?php

namespace App\Filament\Resources\TrendAbnormalities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TrendAbnormalitiesTable
{
    public static function configure(Table $table): Table
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $table
            ->columns([
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),
                TextColumn::make('month')
                    ->label('Bulan')
                    ->formatStateUsing(fn ($state) => $months[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('zone_label')
                    ->label('Zona')
                    ->badge()
                    ->sortable(),
                TextColumn::make('temuan')
                    ->label('Temuan (X)')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('tindak_lanjut')
                    ->label('Selesai (O)')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('belum_selesai')
                    ->label('Belum Selesai (Δ)')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(
                        collect(range(now()->year - 2, now()->year + 1))
                            ->mapWithKeys(fn ($y) => [$y => $y])
                            ->toArray()
                    ),
                SelectFilter::make('zone_label')
                    ->label('Zona')
                    ->options(['Z-1' => 'Z-1', 'Z-2' => 'Z-2', 'Z-3' => 'Z-3']),
            ])
            ->defaultSort('year', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
