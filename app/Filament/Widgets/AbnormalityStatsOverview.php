<?php

namespace App\Filament\Widgets;

use App\Domains\VisualBoard\Models\Abnormality;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AbnormalityStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $totalBulanIni = Abnormality::whereMonth('date_found', $currentMonth)
            ->whereYear('date_found', $currentYear)
            ->count();

        $resolved = Abnormality::whereMonth('date_found', $currentMonth)
            ->whereYear('date_found', $currentYear)
            ->where('status', 'resolved')
            ->count();

        $open = Abnormality::whereMonth('date_found', $currentMonth)
            ->whereYear('date_found', $currentYear)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $percentage = $totalBulanIni > 0 ? round(($resolved / $totalBulanIni) * 100, 1) : 0;

        return [
            Stat::make('X — Temuan Baru', $totalBulanIni)
                ->description('Total abnormality dilaporkan bulan ini')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('danger'),
            
            Stat::make('O — Selesai (Resolved)', $resolved)
                ->description("{$percentage}% tingkat penyelesaian")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('△ — Belum Selesai', $open)
                ->description('Akumulasi (Open/In Progress)')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),
        ];
    }
}
