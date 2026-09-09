<?php

namespace App\Filament\Resources\InspectionCriterias\Pages;

use App\Filament\Resources\InspectionCriterias\InspectionCriteriaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditInspectionCriteria extends EditRecord
{
    protected static string $resource = InspectionCriteriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
