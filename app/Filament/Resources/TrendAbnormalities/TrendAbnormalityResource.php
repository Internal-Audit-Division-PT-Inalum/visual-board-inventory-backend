<?php

namespace App\Filament\Resources\TrendAbnormalities;

use App\Domains\VisualBoard\Models\TrendAbnormality;
use App\Filament\Resources\TrendAbnormalities\Pages\CreateTrendAbnormality;
use App\Filament\Resources\TrendAbnormalities\Pages\EditTrendAbnormality;
use App\Filament\Resources\TrendAbnormalities\Pages\ListTrendAbnormalities;
use App\Filament\Resources\TrendAbnormalities\Schemas\TrendAbnormalityForm;
use App\Filament\Resources\TrendAbnormalities\Tables\TrendAbnormalitiesTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TrendAbnormalityResource extends Resource
{
    protected static ?string $model = TrendAbnormality::class;

    protected static ?string $modelLabel = 'Trend Abnormality';

    protected static ?string $pluralModelLabel = 'Data Trend Abnormality';

    protected static string|\UnitEnum|null $navigationGroup = 'Visual Board 5R';

    protected static ?int $navigationSort = 16;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    /**
     * Data Trend Abnormality kini dihitung OTOMATIS dari tabel abnormalities.
     * Resource ini disembunyikan karena input manual tidak diperlukan lagi.
     * Tabel trend_abnormalities dipertahankan sebagai arsip historis.
     */
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'zone_label';

    public static function form(Schema $schema): Schema
    {
        return TrendAbnormalityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrendAbnormalitiesTable::configure($table);
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
            'index' => ListTrendAbnormalities::route('/'),
            'create' => CreateTrendAbnormality::route('/create'),
            'edit' => EditTrendAbnormality::route('/{record}/edit'),
        ];
    }
}
