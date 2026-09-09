<?php

namespace App\Filament\Resources\InspectionCriterias;

use App\Domains\VisualBoard\Models\InspectionCriteria;
use App\Filament\Resources\InspectionCriterias\Pages\CreateInspectionCriteria;
use App\Filament\Resources\InspectionCriterias\Pages\EditInspectionCriteria;
use App\Filament\Resources\InspectionCriterias\Pages\ListInspectionCriterias;
use App\Filament\Resources\InspectionCriterias\Schemas\InspectionCriteriaForm;
use App\Filament\Resources\InspectionCriterias\Tables\InspectionCriteriasTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InspectionCriteriaResource extends Resource
{
    protected static ?string $model = InspectionCriteria::class;

    protected static ?string $modelLabel = 'Kriteria Inspeksi';

    protected static ?string $pluralModelLabel = 'Kriteria Inspeksi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Visual Board 5R';

    protected static ?string $recordTitleAttribute = 'criteria';

    public static function form(Schema $schema): Schema
    {
        return InspectionCriteriaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InspectionCriteriasTable::configure($table);
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
            'index' => ListInspectionCriterias::route('/'),
            'create' => CreateInspectionCriteria::route('/create'),
            'edit' => EditInspectionCriteria::route('/{record}/edit'),
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
