<?php

namespace App\Filament\Resources\MasterWorkstationCriterias;

use App\Domains\VisualBoard\Models\MasterWorkstationCriteria;
use App\Filament\Resources\MasterWorkstationCriterias\Pages\CreateMasterWorkstationCriteria;
use App\Filament\Resources\MasterWorkstationCriterias\Pages\EditMasterWorkstationCriteria;
use App\Filament\Resources\MasterWorkstationCriterias\Pages\ListMasterWorkstationCriterias;
use App\Filament\Resources\MasterWorkstationCriterias\Schemas\MasterWorkstationCriteriaForm;
use App\Filament\Resources\MasterWorkstationCriterias\Tables\MasterWorkstationCriteriasTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MasterWorkstationCriteriaResource extends Resource
{
    protected static ?string $model = MasterWorkstationCriteria::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Sumber Daya Manusia';

    protected static ?string $modelLabel = 'Kriteria Standar Meja 5R';

    protected static ?string $pluralModelLabel = 'Kriteria Standar Meja 5R';

    public static function form(Schema $schema): Schema
    {
        return MasterWorkstationCriteriaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterWorkstationCriteriasTable::configure($table);
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
            'index' => ListMasterWorkstationCriterias::route('/'),
            'create' => CreateMasterWorkstationCriteria::route('/create'),
            'edit' => EditMasterWorkstationCriteria::route('/{record}/edit'),
        ];
    }
}
