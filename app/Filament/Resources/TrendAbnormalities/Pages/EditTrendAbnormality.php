<?php

namespace App\Filament\Resources\TrendAbnormalities\Pages;

use App\Filament\Resources\TrendAbnormalities\TrendAbnormalityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrendAbnormality extends EditRecord
{
    protected static string $resource = TrendAbnormalityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
