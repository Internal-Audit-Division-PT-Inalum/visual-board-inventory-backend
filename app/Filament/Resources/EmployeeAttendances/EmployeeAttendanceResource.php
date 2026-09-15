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

    protected static ?string $modelLabel = 'Kehadiran Pegawai';

    protected static ?string $pluralModelLabel = 'Data Kehadiran';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Sumber Daya Manusia';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'date';

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
