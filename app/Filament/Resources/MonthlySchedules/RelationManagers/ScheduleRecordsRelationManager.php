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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

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
                Action::make('fill_daily_checklist')
                    ->label('📝 Isi Ceklis Harian')
                    ->color('primary')
                    ->modalWidth('4xl')
                    ->modalSubmitActionLabel('Simpan Ceklis')
                    ->form(function (RelationManager $livewire) {
                        $monthlySchedule = $livewire->getOwnerRecord();
                        $records = ScheduleRecord::where('monthly_schedule_id', $monthlySchedule->id)
                            ->with(['criteria', 'workstation', 'masterWorkstationCriteria'])
                            ->get()
                            ->sortBy(function ($record) {
                                $orderMap = [
                                    'Tidak ada barang yang tidak digunakan' => 1,
                                    'Barang / Peralatan terletak di area label' => 2,
                                    'Isi dalam rak bersih dari debu' => 4,
                                    'Kursi memiliki identitas' => 5,
                                    'Setiap kursi memiliki identitas' => 5,
                                    'Kursi diposisi layout jika tidak digunakan' => 6,
                                    'Setiap kursi diposisi layout jika tidak digunakan' => 6,
                                    'Bersih dari debu / kotoran' => 7,
                                    'Barang mudah bergerak terletak didalam line' => 8,
                                    'Line pembatas dalam keadaan baik' => 9,
                                    'Bersih dari debu / kotoran / genangan air' => 10,
                                    'Kondisi papan bersih dari debu dan kotoran' => 11,
                                    'Lampu dapat berfungsi semua' => 12,
                                    'Tidak ada sarang laba-laba' => 13,
                                    'Kabel tertata rapi' => 14,
                                    'Tembok tidak kotor dan tidak bebercak' => 15,
                                    'Tembok tidak ada sarang laba-laba' => 16,
                                    'Poster / Informasi dalam kondisi update dan bersih' => 17,
                                    'Air tersedia' => 18,
                                    'Body lemari Bersih dari debu / kotoran' => 19,
                                    'Terdapat standar isi lemari' => 20,
                                    'Terdapat label isi lemari' => 21,
                                    'Label dalam keadaan baik' => 22,
                                    'Isi kulkas, galon dan air dispenser tersedia' => 23,
                                    'APAR dalam Kondisi standby (garis hijau)/ P3K Stand By' => 24,
                                    'Tidak ada barang dibawah Apar/ P3K ada ditempatnya' => 25,
                                ];

                                if ($record->criteria) {
                                    $descOrder = str_pad($orderMap[$record->criteria->description] ?? 99, 2, '0', STR_PAD_LEFT);

                                    return $record->criteria->item_group . '_' . $record->criteria->criteria_code . '_' . $descOrder;
                                } elseif ($record->workstation && $record->masterWorkstationCriteria) {
                                    $descOrder = str_pad($orderMap[$record->masterWorkstationCriteria->standard_criteria] ?? 99, 2, '0', STR_PAD_LEFT);

                                    return 'Meja Kerja: ' . $record->workstation->name . '_' . $record->masterWorkstationCriteria->criteria_code . '_' . $descOrder;
                                }

                                return 'ZZZ';
                            });

                        $formComponents = [
                            Section::make('Pilih Waktu Inspeksi')
                                ->description('Tentukan tanggal untuk mencatat hasil aktivitas 5R.')
                                ->icon('heroicon-o-calendar-days')
                                ->schema([
                                    Select::make('day')
                                        ->label('Tanggal Inspeksi')
                                        ->options(array_combine(range(1, 31), range(1, 31)))
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($set, $state) use ($records) {
                                            if (blank($state)) {
                                                return;
                                            }

                                            foreach ($records as $record) {
                                                $daysData = $record->days_data ?? [];
                                                $set("record_{$record->id}", $daysData[$state] ?? null);
                                            }
                                        })
                                        ->columnSpanFull(),
                                ])
                                ->collapsible()
                                ->compact(),
                        ];

                        if ($records->isEmpty()) {
                            $formComponents[] = Placeholder::make('empty')
                                ->label('')
                                ->content('Belum ada kriteria di jadwal ini. Silakan klik "Generate Kriteria Aktif" terlebih dahulu.');

                            return $formComponents;
                        }

                        // Group criteria
                        $generalFields = [];
                        $workstationFields = [];

                        foreach ($records as $record) {
                            if ($record->criteria) {
                                $label = "[{$record->criteria->item_group}] {$record->criteria->criteria_code} - {$record->criteria->description}";
                                $targetArray = &$generalFields;
                            } elseif ($record->workstation && $record->masterWorkstationCriteria) {
                                $label = "[Meja {$record->workstation->name}] {$record->masterWorkstationCriteria->criteria_code} - {$record->masterWorkstationCriteria->standard_criteria}";
                                $targetArray = &$workstationFields;
                            } else {
                                continue;
                            }

                            $targetArray[] = ToggleButtons::make("record_{$record->id}")
                                ->label($label)
                                ->options([
                                    'ok_tanpa_5r' => 'OK Tanpa 5R',
                                    'ok_dengan_5r' => 'OK Dengan 5R',
                                    'abnormal' => 'Abnormal',
                                ])
                                ->colors([
                                    'ok_tanpa_5r' => 'success',
                                    'ok_dengan_5r' => 'warning',
                                    'abnormal' => 'danger',
                                ])
                                ->icons([
                                    'ok_tanpa_5r' => 'heroicon-o-check-circle',
                                    'ok_dengan_5r' => 'heroicon-o-exclamation-triangle',
                                    'abnormal' => 'heroicon-o-x-circle',
                                ])
                                ->inline()
                                ->columnSpanFull();
                        }

                        if (! empty($generalFields)) {
                            $formComponents[] = Section::make('Kriteria Area Umum')
                                ->description('Beri penilaian pada area fasilitas umum.')
                                ->icon('heroicon-o-building-office')
                                ->schema($generalFields)
                                ->hidden(fn ($get) => blank($get('day')));
                        }

                        if (! empty($workstationFields)) {
                            $formComponents[] = Section::make('Kriteria Meja Kerja')
                                ->description('Beri penilaian spesifik pada tiap-tiap meja kerja karyawan.')
                                ->icon('heroicon-o-computer-desktop')
                                ->schema($workstationFields)
                                ->hidden(fn ($get) => blank($get('day')));
                        }

                        return $formComponents;
                    })
                    ->action(function (array $data, RelationManager $livewire) {
                        $day = $data['day'];
                        $monthlySchedule = $livewire->getOwnerRecord();
                        $records = ScheduleRecord::where('monthly_schedule_id', $monthlySchedule->id)->get();

                        DB::transaction(function () use ($data, $day, $records) {
                            foreach ($records as $record) {
                                $fieldKey = "record_{$record->id}";
                                if (array_key_exists($fieldKey, $data)) {
                                    $status = $data[$fieldKey];

                                    $daysData = $record->days_data ?? [];
                                    if (blank($status)) {
                                        unset($daysData[$day]);
                                    } else {
                                        $daysData[$day] = $status;
                                    }

                                    $record->update(['days_data' => $daysData]);
                                }
                            }
                        });

                        Notification::make()
                            ->title('Berhasil')
                            ->body("Ceklis harian untuk tanggal {$day} berhasil disimpan.")
                            ->success()
                            ->send();
                    }),
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
