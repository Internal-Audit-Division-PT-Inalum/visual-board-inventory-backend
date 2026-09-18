<?php

namespace App\Filament\Resources\Workstations;

use App\Domains\VisualBoard\Models\Workstation;
use App\Filament\Resources\Workstations\Pages\CreateWorkstation;
use App\Filament\Resources\Workstations\Pages\EditWorkstation;
use App\Filament\Resources\Workstations\Pages\ListWorkstations;
use App\Filament\Resources\Workstations\Schemas\WorkstationForm;
use App\Filament\Resources\Workstations\Tables\WorkstationTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class WorkstationResource extends Resource
{
    protected static ?string $model = Workstation::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventaris';

    protected static ?int $navigationSort = 24;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $modelLabel = 'Meja (Workstation)';

    protected static ?string $pluralModelLabel = 'Daftar Meja';

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
            'index' => ListWorkstations::route('/'),
            'create' => CreateWorkstation::route('/create'),
            'edit' => EditWorkstation::route('/{record}/edit'),
        ];
    }
}
