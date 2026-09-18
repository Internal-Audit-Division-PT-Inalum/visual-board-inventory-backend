<?php

namespace App\Filament\Resources\GeneralDocuments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GeneralDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('document')
                    ->collection('document')
                    ->label('Preview'),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('domain')
                    ->label('Tampil di Tab')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'visual_board' => '📊 Tab General',
                        'organization' => '🏢 Tab Organisasi',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'visual_board' => 'info',
                        'organization' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'basic_rule' => 'Basic Rule',
                        'flow_process' => 'Flow Process',
                        'kaizen_report' => 'Kaizen Report',
                        'structure' => 'Bagan Struktur',
                        'map_area' => 'Map Area 5R',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'basic_rule' => 'info',
                        'flow_process' => 'warning',
                        'kaizen_report' => 'success',
                        'structure' => 'primary',
                        'map_area' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('domain')
                    ->label('Tab Tujuan')
                    ->options([
                        'visual_board' => 'Tab General (5R)',
                        'organization' => 'Tab Organisasi',
                    ]),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'basic_rule' => 'Basic Rule',
                        'flow_process' => 'Flow Process',
                        'kaizen_report' => 'Kaizen Report',
                        'structure' => 'Bagan Struktur',
                        'map_area' => 'Map Area 5R',
                    ]),
            ])
            ->defaultSort('sort_order')
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
