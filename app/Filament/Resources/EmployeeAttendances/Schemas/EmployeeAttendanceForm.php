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
                    ->label('Pegawai')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'namecode',
                        modifyQueryUsing: fn ($query) => $query->join('users', 'users.id', '=', 'employees.user_id')->select('employees.*')->with('user')
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user ? "{$record->user->name} ({$record->namecode})" : $record->namecode)
                    ->required()
                    ->searchable(['employees.namecode', 'users.name'])
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
                        'business_trip' => 'Perjalanan Dinas',
                    ])
                    ->required()
                    ->searchable(),
                Textarea::make('notes')
                    ->label('Catatan Tambahan')
                    ->columnSpanFull(),
            ]);
    }
}
