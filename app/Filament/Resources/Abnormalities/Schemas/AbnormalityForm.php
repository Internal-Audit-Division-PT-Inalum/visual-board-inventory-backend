<?php

namespace App\Filament\Resources\Abnormalities\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Schema;

class AbnormalityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('zone_id')
                    ->label('Zona')
                    ->relationship('zone', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('monthly_schedule_id')
                    ->label('Jadwal Bulanan')
                    ->relationship('monthlySchedule', 'id')
                    ->searchable()
                    ->preload(),
                Select::make('inspection_criteria_id')
                    ->label('Kriteria Inspeksi (Grup / Standar)')
                    ->relationship('criteria', 'description')
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => "[{$record->item_group}] {$record->criteria_code} — {$record->description}"
                    )
                    ->searchable()
                    ->preload(),
                DatePicker::make('date_found')
                    ->label('Tanggal Ditemukan')
                    ->required(),
                DatePicker::make('target_date')
                    ->label('Target Selesai'),
                DatePicker::make('actual_resolution_date')
                    ->label('Tanggal Selesai Aktual'),
                Textarea::make('problem_description')
                    ->label('Deskripsi Masalah')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('countermeasure_plan')
                    ->label('Rencana Penanggulangan')
                    ->columnSpanFull(),
                Textarea::make('countermeasure_actual')
                    ->label('Aktual Penanggulangan')
                    ->columnSpanFull(),
                Select::make('pic_id')
                    ->label('PIC (Penanggung Jawab)')
                    ->relationship('pic', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('reported_by_id')
                    ->label('Penemu (Ditemukan Oleh)')
                    ->relationship('reportedBy', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Terbuka (Open)',
                        'in_progress' => 'Sedang Diproses (In Progress)',
                        'resolved' => 'Selesai (Resolved)',
                    ])
                    ->required()
                    ->default('open'),
                TextInput::make('progress_percentage')
                    ->label('Persentase Progres (%)')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_kaizen')
                    ->label('Kaizen (Improvement)')
                    ->live()
                    ->required(),
                SpatieMediaLibraryFileUpload::make('kaizen_reports')
                    ->label('Dokumen Laporan Kaizen (PDF/Excel)')
                    ->collection('kaizen_reports')
                    ->acceptedFileTypes(['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->visible(fn ($get) => $get('is_kaizen'))
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('evidence_photos')
                    ->label('Foto Bukti Temuan (Sebelum Perbaikan)')
                    ->collection('evidence_photos')
                    ->image()
                    ->imageEditor()
                    ->multiple()
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('resolution_photos')
                    ->label('Foto Setelah Perbaikan')
                    ->collection('resolution_photos')
                    ->image()
                    ->imageEditor()
                    ->multiple()
                    ->columnSpanFull(),
            ]);
    }
}
