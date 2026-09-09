<?php

namespace App\Filament\Resources\InspectionCriterias\Pages;

use App\Filament\Resources\InspectionCriterias\InspectionCriteriaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInspectionCriterias extends ListRecords
{
    protected static string $resource = InspectionCriteriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
