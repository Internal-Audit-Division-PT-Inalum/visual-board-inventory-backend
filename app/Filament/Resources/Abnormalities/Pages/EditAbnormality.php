<?php

namespace App\Filament\Resources\Abnormalities\Pages;

use App\Filament\Resources\Abnormalities\AbnormalityResource;
use App\Services\VisualBoard\AbnormalityService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAbnormality extends EditRecord
{
    protected static string $resource = AbnormalityResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(AbnormalityService::class)->updateAbnormality($record->id, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
