<?php

namespace App\Filament\Resources\MonthlySchedules;

use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Filament\Resources\MonthlySchedules\Pages\CreateMonthlySchedule;
use App\Filament\Resources\MonthlySchedules\Pages\EditMonthlySchedule;
use App\Filament\Resources\MonthlySchedules\Pages\ListMonthlySchedules;
use App\Filament\Resources\MonthlySchedules\RelationManagers\SchedulePicsRelationManager;
use App\Filament\Resources\MonthlySchedules\RelationManagers\ScheduleRecordsRelationManager;
use App\Filament\Resources\MonthlySchedules\Schemas\MonthlyScheduleForm;
use App\Filament\Resources\MonthlySchedules\Tables\MonthlySchedulesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MonthlyScheduleResource extends Resource
{
    protected static ?string $model = MonthlySchedule::class;

    protected static ?string $modelLabel = 'Jadwal Bulanan';

    protected static ?string $pluralModelLabel = 'Jadwal Bulanan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Visual Board 5R';

    protected static ?string $recordTitleAttribute = 'month';

    public static function form(Schema $schema): Schema
    {
        return MonthlyScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonthlySchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ScheduleRecordsRelationManager::class,
            SchedulePicsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonthlySchedules::route('/'),
            'create' => CreateMonthlySchedule::route('/create'),
            'edit' => EditMonthlySchedule::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
