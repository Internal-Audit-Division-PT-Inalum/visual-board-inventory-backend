<?php

namespace App\Filament\Widgets;

use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Models\MonthlySchedule;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExecutiveStatCardsWidget extends BaseWidget
{
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Simulasi perhitungan data nyata
        $abnormalitasTerbuka = Abnormality::where('status', 'open')->count();
        $abnormalitasSelesai = Abnormality::where('status', 'resolved')->count();
        $abnormalitasDalamProses = Abnormality::where('status', 'in_progress')->count();

        return [
            Stat::make('Objek Risiko Tinggi', $abnormalitasTerbuka)
                ->description('Membutuhkan perhatian segera')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->url(route('filament.admin.resources.abnormalities.index', ['tableFilters[status][value]' => 'open']))
                ->extraAttributes([
                    'class' => 'border-t-4 border-rose-500 shadow-sm bg-white dark:bg-gray-900',
                ]),

            Stat::make('Jadwal Inspeksi Disetujui', MonthlySchedule::where('status', 'disetujui')->count())
                ->description('Periode bulan ini')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(route('filament.admin.resources.monthly-schedules.index'))
                ->extraAttributes([
                    'class' => 'border-t-4 border-emerald-500 shadow-sm bg-white dark:bg-gray-900',
                ]),

            Stat::make('Temuan Sedang Diproses', $abnormalitasDalamProses)
                ->description('Dalam perbaikan PIC')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning')
                ->url(route('filament.admin.resources.abnormalities.index', ['tableFilters[status][value]' => 'in_progress']))
                ->extraAttributes([
                    'class' => 'border-t-4 border-amber-500 shadow-sm bg-white dark:bg-gray-900',
                ]),

            Stat::make('Temuan Diselesaikan', $abnormalitasSelesai)
                ->description('Total bulan ini')
                ->descriptionIcon('heroicon-m-hand-thumb-up')
                ->color('primary')
                ->url(route('filament.admin.resources.abnormalities.index', ['tableFilters[status][value]' => 'resolved']))
                ->extraAttributes([
                    'class' => 'border-t-4 border-indigo-500 shadow-sm bg-white dark:bg-gray-900',
                ]),
        ];
    }
}
