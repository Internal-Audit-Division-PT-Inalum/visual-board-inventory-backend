<?php

namespace App\Filament\Resources\MonthlySchedules\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScheduleRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'scheduleRecords';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('inspection_criteria_id')
                    ->label('Kriteria Inspeksi')
                    ->relationship('criteria', 'description')
                    ->searchable()
                    ->preload()
                    ->required(),
                ViewField::make('days_data')
                    ->label('Pengisian Ceklis Harian 5R')
                    ->view('filament.forms.components.daily-grid-input')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('inspection_criteria_id')
            ->columns([
                TextColumn::make('criteria.item_group')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('criteria.criteria_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('criteria.description')
                    ->label('Standar 5R')
                    ->limit(50),
                TextColumn::make('days_data_summary')
                    ->label('Status Pengisian')
                    ->getStateUsing(function ($record) {
                        $state = $record->days_data;
                        if (! is_array($state) || empty(array_filter($state))) {
                            return 'Belum ada isian';
                        }
                        $count = count(array_filter($state));

                        return "{$count} hari terisi";
                    })
                    ->badge()
                    ->color(function ($state) {
                        return $state === 'Belum ada isian' ? 'gray' : 'success';
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
