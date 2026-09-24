<?php

namespace App\Filament\Resources\MasterWorkstationCriterias\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MasterWorkstationCriteriasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item_group')
                    ->label('Grup/Area')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('criteria_code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('standard_criteria')
                    ->label('Standar Penilaian')
                    ->searchable()
                    ->wrap(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
