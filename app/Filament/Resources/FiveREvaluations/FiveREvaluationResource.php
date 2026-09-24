<?php

namespace App\Filament\Resources\FiveREvaluations;

use App\Domains\VisualBoard\Models\FiveREvaluation;
use App\Filament\Resources\FiveREvaluations\Pages\CreateFiveREvaluation;
use App\Filament\Resources\FiveREvaluations\Pages\EditFiveREvaluation;
use App\Filament\Resources\FiveREvaluations\Pages\ListFiveREvaluations;
use App\Filament\Resources\FiveREvaluations\Schemas\FiveREvaluationForm;
use App\Filament\Resources\FiveREvaluations\Tables\FiveREvaluationsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FiveREvaluationResource extends Resource
{
    protected static ?string $model = FiveREvaluation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Asesmen IIA';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Asesmen IIA';

    protected static ?string $pluralModelLabel = 'Data Asesmen IIA';

    public static function getNavigationGroup(): ?string
    {
        return 'Visual Board 5R';
    }

    public static function form(Schema $schema): Schema
    {
        return FiveREvaluationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FiveREvaluationsTable::configure($table);
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
            'index' => ListFiveREvaluations::route('/'),
            'create' => CreateFiveREvaluation::route('/create'),
            'edit' => EditFiveREvaluation::route('/{record}/edit'),
        ];
    }
}
