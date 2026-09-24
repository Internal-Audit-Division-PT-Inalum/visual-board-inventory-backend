<?php

namespace App\Filament\Resources\MasterWorkstationCriterias\Pages;

use App\Filament\Resources\MasterWorkstationCriterias\MasterWorkstationCriteriaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterWorkstationCriteria extends EditRecord
{
    protected static string $resource = MasterWorkstationCriteriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
