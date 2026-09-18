<?php

namespace App\Filament\Resources\Bulletins\QuickLinks;

use App\Domains\Portal\Models\QuickLink;
use App\Filament\Resources\Bulletins\QuickLinks\Pages\CreateQuickLink;
use App\Filament\Resources\Bulletins\QuickLinks\Pages\EditQuickLink;
use App\Filament\Resources\Bulletins\QuickLinks\Pages\ListQuickLinks;
use App\Filament\Resources\Bulletins\QuickLinks\Schemas\QuickLinkForm;
use App\Filament\Resources\Bulletins\QuickLinks\Tables\QuickLinksTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class QuickLinkResource extends Resource
{
    protected static ?string $model = QuickLink::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Portal & Mading';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return QuickLinkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuickLinksTable::configure($table);
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
            'index' => ListQuickLinks::route('/'),
            'create' => CreateQuickLink::route('/create'),
            'edit' => EditQuickLink::route('/{record}/edit'),
        ];
    }
}
