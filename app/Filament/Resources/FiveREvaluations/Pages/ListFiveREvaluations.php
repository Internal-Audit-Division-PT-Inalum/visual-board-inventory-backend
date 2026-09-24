<?php

namespace App\Filament\Resources\FiveREvaluations\Pages;

use App\Filament\Resources\FiveREvaluations\FiveREvaluationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFiveREvaluations extends ListRecords
{
    protected static string $resource = FiveREvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
