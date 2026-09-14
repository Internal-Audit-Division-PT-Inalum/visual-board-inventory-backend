<?php

namespace App\Filament\Resources\MonthlySchedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class MonthlyScheduleForm
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
                DatePicker::make('period_month')
                    ->label('Periode (Bulan)')
                    ->native(false)
                    ->displayFormat('F Y')
                    ->required(),
                Select::make('status')
                    ->label('Status Approval')
                    ->options([
                        'draft' => 'Draft',
                        'in_progress' => 'Sedang Berjalan',
                        'completed' => 'Selesai',
                    ])
                    ->required()
                    ->default('draft'),
                Select::make('created_by')
                    ->label('Dibuat Oleh')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id()),
            ]);
    }
}
