<?php

namespace App\Filament\Resources\Abnormalities\Pages;

use App\Filament\Resources\Abnormalities\AbnormalityResource;
use App\Services\VisualBoard\AbnormalityService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAbnormality extends CreateRecord
{
    protected static string $resource = AbnormalityResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(AbnormalityService::class)->createAbnormality($data);
    }
}
