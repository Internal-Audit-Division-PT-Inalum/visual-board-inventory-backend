<?php

namespace App\Filament\Resources\Abnormalities;

use App\Domains\VisualBoard\Models\Abnormality;
use App\Filament\Resources\Abnormalities\Pages\CreateAbnormality;
use App\Filament\Resources\Abnormalities\Pages\EditAbnormality;
use App\Filament\Resources\Abnormalities\Pages\ListAbnormalities;
use App\Filament\Resources\Abnormalities\Schemas\AbnormalityForm;
use App\Filament\Resources\Abnormalities\Tables\AbnormalitiesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AbnormalityResource extends Resource
{
    protected static ?string $model = Abnormality::class;

    protected static ?string $modelLabel = 'Abnormalitas';

    protected static ?string $pluralModelLabel = 'Daftar Abnormalitas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Visual Board 5R';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AbnormalityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AbnormalitiesTable::configure($table);
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
            'index' => ListAbnormalities::route('/'),
            'create' => CreateAbnormality::route('/create'),
            'edit' => EditAbnormality::route('/{record}/edit'),
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
