<?php

namespace App\Filament\Resources\Workstations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScheduleRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'scheduleRecords';

    protected static ?string $title = 'Riwayat Penilaian 5R (Check Sheet)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Readonly form just for viewing if needed
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('monthlySchedule.period_month')
                    ->label('Periode (Bulan/Tahun)')
                    ->date('F Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('masterWorkstationCriteria.item_group')
                    ->label('Area/Grup')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('masterWorkstationCriteria.criteria_code')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('masterWorkstationCriteria.standard_criteria')
                    ->label('Standar Penilaian')
                    ->wrap(),
                TextColumn::make('days_data')
                    ->label('Data Evaluasi')
                    ->formatStateUsing(fn (string $state) => strlen($state) > 2 ? 'Sudah ada evaluasi' : 'Belum dievaluasi')
                    ->badge()
                    ->color(fn (string $state) => strlen($state) > 2 ? 'success' : 'gray'),
            ])
            ->defaultSort('monthlySchedule.period_month', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
