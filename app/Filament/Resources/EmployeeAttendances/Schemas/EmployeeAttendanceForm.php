<?php

namespace App\Filament\Resources\EmployeeAttendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EmployeeAttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Karyawan')
                    ->relationship('employee', 'namecode')
                    ->required()
                    ->searchable()
                    ->preload(),
                DatePicker::make('date')
                    ->label('Tanggal')
                    ->required(),
                Select::make('status')
                    ->label('Status Kehadiran')
                    ->options([
                        'present' => 'Hadir',
                        'leave' => 'Cuti',
                        'sick' => 'Sakit',
                        'trip' => 'Perjalanan Dinas',
                    ])
                    ->required()
                    ->searchable(),
                Textarea::make('notes')
                    ->label('Catatan Tambahan')
                    ->columnSpanFull(),
            ]);
    }
}
