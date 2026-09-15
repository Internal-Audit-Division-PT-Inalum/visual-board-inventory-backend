<?php

namespace App\Filament\Resources;

use App\Domains\VisualBoard\Models\Workstation;
use App\Filament\Resources\WorkstationResource\Pages;
use App\Filament\Resources\WorkstationResource\RelationManagers;
use App\Filament\Resources\Workstations\Schemas\WorkstationForm;
use App\Filament\Resources\Workstations\Tables\WorkstationTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class WorkstationResource extends Resource
{
    protected static ?string $model = Workstation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected static \UnitEnum|string|null $navigationGroup = 'Visual Board 5R';

    protected static ?string $modelLabel = 'Meja (Workstation)';

    protected static ?string $pluralModelLabel = 'Daftar Meja';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return WorkstationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkstationTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkstations::route('/'),
            'create' => Pages\CreateWorkstation::route('/create'),
            'edit' => Pages\EditWorkstation::route('/{record}/edit'),
        ];
    }
}
