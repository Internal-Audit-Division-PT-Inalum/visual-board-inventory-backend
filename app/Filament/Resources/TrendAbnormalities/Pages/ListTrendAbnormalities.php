<?php

namespace App\Filament\Resources\TrendAbnormalities\Pages;

use App\Filament\Resources\TrendAbnormalities\TrendAbnormalityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrendAbnormalities extends ListRecords
{
    protected static string $resource = TrendAbnormalityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
