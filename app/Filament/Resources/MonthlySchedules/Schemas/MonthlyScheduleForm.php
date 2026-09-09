<?php

namespace App\Filament\Resources\MonthlySchedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                    ->required(),
                TextInput::make('status')
                    ->label('Status Approval')
                    ->required()
                    ->default('draft'),
                TextInput::make('approval_data')
                    ->label('Data Approval (JSON)')
                    ->required()
                    ->default('{}'),
                TextInput::make('created_by')
                    ->label('Dibuat Oleh (User ID)'),
            ]);
    }
}
