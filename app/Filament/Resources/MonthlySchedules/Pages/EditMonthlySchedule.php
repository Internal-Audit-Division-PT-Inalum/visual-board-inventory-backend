<?php

namespace App\Filament\Resources\MonthlySchedules\Pages;

use App\Filament\Resources\MonthlySchedules\MonthlyScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMonthlySchedule extends EditRecord
{
    protected static string $resource = MonthlyScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
