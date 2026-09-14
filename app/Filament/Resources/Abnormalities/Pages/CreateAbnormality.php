<?php

namespace App\Filament\Resources\Abnormalities\Pages;

use App\Filament\Resources\Abnormalities\AbnormalityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAbnormality extends CreateRecord
{
    protected static string $resource = AbnormalityResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(\App\Services\VisualBoard\AbnormalityService::class)->createAbnormality($data);
    }
}
