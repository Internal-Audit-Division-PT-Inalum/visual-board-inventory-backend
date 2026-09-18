<?php

namespace App\Filament\Resources\Bulletins\QuickLinks\Pages;

use App\Filament\Resources\Bulletins\QuickLinks\QuickLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuickLink extends EditRecord
{
    protected static string $resource = QuickLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
