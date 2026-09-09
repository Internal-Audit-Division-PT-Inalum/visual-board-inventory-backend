<?php

namespace App\Filament\Resources\EmployeeAttendances;

use App\Domains\HR\Models\EmployeeAttendance;
use App\Filament\Resources\EmployeeAttendances\Pages\CreateEmployeeAttendance;
use App\Filament\Resources\EmployeeAttendances\Pages\EditEmployeeAttendance;
use App\Filament\Resources\EmployeeAttendances\Pages\ListEmployeeAttendances;
use App\Filament\Resources\EmployeeAttendances\Schemas\EmployeeAttendanceForm;
use App\Filament\Resources\EmployeeAttendances\Tables\EmployeeAttendancesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EmployeeAttendanceResource extends Resource
{
    protected static ?string $model = EmployeeAttendance::class;

    protected static ?string $modelLabel = 'Kehadiran Karyawan';

    protected static ?string $pluralModelLabel = 'Kehadiran Karyawan';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Organisasi & HR';

    public static function form(Schema $schema): Schema
    {
        return EmployeeAttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeAttendancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeAttendances::route('/'),
            'create' => CreateEmployeeAttendance::route('/create'),
            'edit' => EditEmployeeAttendance::route('/{record}/edit'),
        ];
    }
}
