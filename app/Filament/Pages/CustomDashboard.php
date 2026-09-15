<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardHeaderWidget;
use App\Filament\Widgets\ExecutiveStatCardsWidget;
use App\Filament\Widgets\MyWorkListWidget;
use App\Filament\Widgets\PortfolioResolutionWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class CustomDashboard extends BaseDashboard
{
    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function getColumns(): int|array
    {
        return 12; // 12 columns grid for maximum flexibility
    }

    public function getWidgets(): array
    {
        return [
            DashboardHeaderWidget::class,
            ExecutiveStatCardsWidget::class,
            PortfolioResolutionWidget::class,
            MyWorkListWidget::class,
        ];
    }
}
