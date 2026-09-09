<?php

namespace App\Filament\Resources\InventoryLedgers\Pages;

use App\Filament\Resources\InventoryLedgers\InventoryLedgerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInventoryLedgers extends ListRecords
{
    protected static string $resource = InventoryLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
