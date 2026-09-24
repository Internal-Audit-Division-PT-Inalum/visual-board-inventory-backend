<?php

namespace App\Filament\Resources\FiveREvaluations\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FiveREvaluationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('month')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                    ])
                    ->required(),

                Select::make('year')
                    ->label('Tahun')
                    ->options(function () {
                        $currentYear = (int) date('Y');

                        return array_combine(range($currentYear - 2, $currentYear + 2), range($currentYear - 2, $currentYear + 2));
                    })
                    ->required(),

                Select::make('type')
                    ->label('Tipe Evaluasi')
                    ->options([
                        'self_assessment' => 'Self Assessment',
                        'asesor' => 'Asesor',
                    ])
                    ->required(),

                FileUpload::make('evaluation_file')
                    ->label('File Evaluasi (Wajib Excel .xlsx)')
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                    ->directory('five-r-evaluations')
                    ->required()
                    ->helperText('File Excel akan di-parse secara otomatis untuk mengambil Nilai Total.')
                    ->columnSpanFull(),

                TextInput::make('total_score')
                    ->label('Skor Total')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Skor total ini akan diisi secara otomatis dari file Excel.'),
            ]);
    }
}
