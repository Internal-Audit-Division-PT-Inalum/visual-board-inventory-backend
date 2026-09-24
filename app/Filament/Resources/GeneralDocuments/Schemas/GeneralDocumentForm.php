<?php

namespace App\Filament\Resources\GeneralDocuments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class GeneralDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('domain')
                    ->label('Tampil di Tab')
                    ->options([
                        'visual_board' => '📊 Tab General (Dokumen Referensi 5R)',
                        'organization' => '🏢 Tab Organisasi (Bagan Struktur / Map Area)',
                        'assessment' => '📋 Tab Asesmen (Self Assessment & Asesor)',
                    ])
                    ->required()
                    ->default('visual_board')
                    ->live()
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->label('Judul Dokumen')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('category')
                    ->label('Kategori')
                    ->options(fn (Get $get) => match ($get('domain')) {
                        'organization' => [
                            'structure' => 'Bagan Struktur (Organisasi & 5R)',
                            'map_area' => 'Map Area 5R',
                        ],
                        'assessment' => [
                            'self_assessment' => 'Self Assessment',
                            'asesor' => 'Assessment by Asesor',
                        ],
                        default => [ // visual_board
                            'basic_rule' => 'Basic Rule (Aturan Dasar)',
                            'flow_process' => 'Flow Process (Alur Proses)',
                            'kaizen_report' => 'Kaizen Report (Laporan Kaizen)',
                            'sor' => 'SOR (Safety Observation Report)',
                            'cog' => 'COG (Cost of Goods)',
                            'berat_badan' => 'Challenge Weight Loss',
                        ],
                    })
                    ->required()
                    ->searchable()
                    ->live(),

                Select::make('implementation_month')
                    ->label('Bulan Implementasi (Khusus Kaizen)')
                    ->options([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                    ])
                    ->visible(fn (Get $get) => $get('category') === 'kaizen_report')
                    ->required(fn (Get $get) => $get('category') === 'kaizen_report'),

                Select::make('implementation_year')
                    ->label('Tahun Implementasi (Khusus Kaizen)')
                    ->options(function () {
                        $currentYear = (int) date('Y');

                        return array_combine(range($currentYear - 2, $currentYear + 2), range($currentYear - 2, $currentYear + 2));
                    })
                    ->visible(fn (Get $get) => $get('category') === 'kaizen_report')
                    ->required(fn (Get $get) => $get('category') === 'kaizen_report'),

                TextInput::make('sort_order')
                    ->label('Urutan Tampil')
                    ->numeric()
                    ->default(0),

                Textarea::make('description')
                    ->label('Deskripsi (Opsional)')
                    ->maxLength(500)
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('document')
                    ->label('Upload Dokumen (PDF / Gambar)')
                    ->collection('document')
                    ->disk('public')
                    ->required()
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(10240) // 10MB
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Aktif Ditampilkan di Visual Board')
                    ->default(true)
                    ->columnSpanFull(),
            ]);
    }
}
