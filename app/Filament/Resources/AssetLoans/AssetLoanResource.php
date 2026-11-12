<?php

namespace App\Filament\Resources\AssetLoans;

use App\Domains\Inventory\Models\AssetLoan;
use App\Filament\Resources\AssetLoans\Pages\ListAssetLoans;
use App\Filament\Resources\AssetLoans\Tables\AssetLoansTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AssetLoanResource extends Resource
{
    protected static ?string $model = AssetLoan::class;

    protected static ?string $modelLabel = 'Peminjaman Aset';

    protected static ?string $pluralModelLabel = 'Daftar Peminjaman Aset';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventaris';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 24;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return AssetLoansTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['item', 'employee', 'borrowLedger', 'returns']);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssetLoans::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
