<?php

namespace App\Filament\Resources\MonthlySchedules\Pages;

use App\Filament\Resources\MonthlySchedules\MonthlyScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonthlySchedules extends ListRecords
{
    protected static string $resource = MonthlyScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
