<?php

namespace App\Filament\Resources\InventoryLedgers;

use App\Domains\Inventory\Models\InventoryLedger;
use App\Filament\Resources\InventoryLedgers\Pages\ListInventoryLedgers;
use App\Filament\Resources\InventoryLedgers\Schemas\InventoryLedgerForm;
use App\Filament\Resources\InventoryLedgers\Tables\InventoryLedgersTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InventoryLedgerResource extends Resource
{
    protected static ?string $model = InventoryLedger::class;

    protected static ?string $modelLabel = 'Riwayat Transaksi';

    protected static ?string $pluralModelLabel = 'Riwayat Transaksi';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventaris';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'transaction_type';

    public static function form(Schema $schema): Schema
    {
        return InventoryLedgerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryLedgersTable::configure($table);
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
            'index' => ListInventoryLedgers::route('/'),
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
