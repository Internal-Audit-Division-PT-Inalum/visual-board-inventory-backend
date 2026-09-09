<?php

namespace App\Filament\Resources\Abnormalities\Pages;

use App\Filament\Resources\Abnormalities\AbnormalityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAbnormality extends EditRecord
{
    protected static string $resource = AbnormalityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
