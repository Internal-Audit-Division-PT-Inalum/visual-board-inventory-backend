<?php

namespace App\Filament\Resources\MonthlySchedules\RelationManagers;

use App\Domains\VisualBoard\Models\InspectionCriteria;
use App\Domains\VisualBoard\Models\MasterWorkstationCriteria;
use App\Domains\VisualBoard\Models\ScheduleRecord;
use App\Domains\VisualBoard\Models\Workstation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
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
                    ->default([])
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
            ->filters([])
            ->headerActions([
                Action::make('generate_criteria')
                    ->label('Generate Kriteria Aktif')
                    ->color('success')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalDescription('Aksi ini akan menarik semua Kriteria Inspeksi yang aktif di zona ini dan belum ada di daftar, lalu menambahkannya secara otomatis.')
                    ->action(function (RelationManager $livewire) {
                        $monthlySchedule = $livewire->getOwnerRecord();
                        $activeCriteria = InspectionCriteria::where('zone_id', $monthlySchedule->zone_id)
                            ->where('is_active', true)
                            ->get();

                        $existingIds = $monthlySchedule->scheduleRecords()->pluck('inspection_criteria_id')->toArray();

                        $newCount = 0;
                        foreach ($activeCriteria as $criteria) {
                            if (! in_array($criteria->id, $existingIds)) {
                                ScheduleRecord::create([
                                    'monthly_schedule_id' => $monthlySchedule->id,
                                    'inspection_criteria_id' => $criteria->id,
                                    'days_data' => [],
                                ]);
                                $newCount++;
                            }
                        }

                        // Generate untuk Meja (Workstations)
                        $workstations = Workstation::where('zone_id', $monthlySchedule->zone_id)->where('is_active', true)->get();
                        $masterCriteria = MasterWorkstationCriteria::where('is_active', true)->get();

                        foreach ($workstations as $workstation) {
                            foreach ($masterCriteria as $mc) {
                                $exists = ScheduleRecord::where('monthly_schedule_id', $monthlySchedule->id)
                                    ->where('workstation_id', $workstation->id)
                                    ->where('master_workstation_criteria_id', $mc->id)
                                    ->exists();

                                if (! $exists) {
                                    ScheduleRecord::create([
                                        'monthly_schedule_id' => $monthlySchedule->id,
                                        'workstation_id' => $workstation->id,
                                        'master_workstation_criteria_id' => $mc->id,
                                        'days_data' => [],
                                    ]);
                                    $newCount++;
                                }
                            }
                        }

                        Notification::make()
                            ->title('Berhasil')
                            ->body("{$newCount} kriteria aktif ditambahkan.")
                            ->success()
                            ->send();
                    }),
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['days_data'] = $data['days_data'] ?? [];

                        return $data;
                    }),
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
