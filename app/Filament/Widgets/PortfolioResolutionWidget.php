<?php

namespace App\Filament\Widgets;

use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Models\MonthlySchedule;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PortfolioResolutionWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Simulasi kalkulasi
        $totalAbnormality = Abnormality::count();
        $resolvedAbnormality = Abnormality::where('status', 'resolved')->count();
        $resolutionRate = $totalAbnormality > 0 ? round(($resolvedAbnormality / $totalAbnormality) * 100) : 0;

        $pendingApproval = MonthlySchedule::where('status', 'menunggu_persetujuan')->count();

        return [
            Stat::make('Resolution Rate', $resolutionRate . '%')
                ->description('Tingkat penyelesaian 5R')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('white')
                ->url(route('filament.admin.resources.abnormalities.index'))
                ->extraAttributes([
                    'class' => 'bg-indigo-600 rounded-xl shadow-md border-0 [&_*]:!text-white',
                ]),

            Stat::make('Jadwal Menunggu Approval', $pendingApproval)
                ->description('Butuh tindakan segera')
                ->descriptionIcon('heroicon-m-clock')
                ->color('white')
                ->url(route('filament.admin.resources.monthly-schedules.index', ['tableFilters[status][value]' => 'menunggu_persetujuan']))
                ->extraAttributes([
                    'class' => 'bg-emerald-600 rounded-xl shadow-md border-0 [&_*]:!text-white',
                ]),

            Stat::make('Kaizen Diimplementasi', Abnormality::where('is_kaizen', true)->count())
                ->description('Ide perbaikan diterapkan')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('white')
                ->url(route('filament.admin.resources.abnormalities.index', ['tableFilters[is_kaizen][value]' => '1']))
                ->extraAttributes([
                    'class' => 'bg-amber-500 rounded-xl shadow-md border-0 [&_*]:!text-white',
                ]),
        ];
    }
}
