<?php

namespace App\Filament\Resources\GeneralDocuments;

use App\Domains\VisualBoard\Models\GeneralDocument;
use App\Filament\Resources\GeneralDocuments\Pages\CreateGeneralDocument;
use App\Filament\Resources\GeneralDocuments\Pages\EditGeneralDocument;
use App\Filament\Resources\GeneralDocuments\Pages\ListGeneralDocuments;
use App\Filament\Resources\GeneralDocuments\Schemas\GeneralDocumentForm;
use App\Filament\Resources\GeneralDocuments\Tables\GeneralDocumentsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class GeneralDocumentResource extends Resource
{
    protected static ?string $model = GeneralDocument::class;

    protected static ?string $modelLabel = 'Dokumen Media';

    protected static ?string $pluralModelLabel = 'Manajemen Dokumen Media';

    protected static string|\UnitEnum|null $navigationGroup = 'Visual Board 5R';

    protected static ?int $navigationSort = 44;

    protected static ?string $navigationLabel = 'Dokumen Media';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return GeneralDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeneralDocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGeneralDocuments::route('/'),
            'create' => CreateGeneralDocument::route('/create'),
            'edit' => EditGeneralDocument::route('/{record}/edit'),
        ];
    }
}
