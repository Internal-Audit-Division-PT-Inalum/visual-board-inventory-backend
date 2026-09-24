<?php

namespace App\Filament\Resources\MasterWorkstationCriterias\Pages;

use App\Filament\Resources\MasterWorkstationCriterias\MasterWorkstationCriteriaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterWorkstationCriterias extends ListRecords
{
    protected static string $resource = MasterWorkstationCriteriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
