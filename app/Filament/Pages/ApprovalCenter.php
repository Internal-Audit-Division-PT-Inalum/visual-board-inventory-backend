<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ApprovalCenter extends Page
{
    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Approval Center';
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-check-badge';
    }

    public static function getNavigationLabel(): string
    {
        return 'Approval Center';
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Visual Board 5R';
    }



    public function getHeading(): string|Htmlable
    {
        return 'Approval Center';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PendingMonthlySchedulesTable::class,
            \App\Filament\Widgets\PendingAbnormalitiesTable::class,
        ];
    }
}
