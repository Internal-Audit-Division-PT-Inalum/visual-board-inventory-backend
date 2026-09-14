<?php

namespace App\Filament\Widgets;

use App\Domains\VisualBoard\Models\Abnormality;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AbnormalityTrendChart extends ChartWidget
{
    protected ?string $heading = 'Tren Abnormalitas (5R)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $currentYear = now()->year;

        // X = Total temuan baru per bulan (semua status, berdasarkan date_found)
        $newFindings = Abnormality::select(
            DB::raw('EXTRACT(MONTH FROM date_found) as month'),
            DB::raw('count(*) as count')
        )
            ->whereYear('date_found', $currentYear)
            ->groupBy('month')
            ->pluck('count', 'month');

        // O = Temuan yang sudah resolved per bulan (berdasarkan actual_resolution_date)
        $resolvedFindings = Abnormality::select(
            DB::raw('EXTRACT(MONTH FROM actual_resolution_date) as month'),
            DB::raw('count(*) as count')
        )
            ->whereYear('actual_resolution_date', $currentYear)
            ->where('status', 'resolved')
            ->whereNotNull('actual_resolution_date')
            ->groupBy('month')
            ->pluck('count', 'month');

        // △ = Temuan yang masih belum selesai per bulan (akumulatif)
        $unresolvedPerMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $unresolvedPerMonth[$m] = Abnormality::where('date_found', '<=', Carbon::create($currentYear, $m)->endOfMonth())
                ->whereIn('status', ['open', 'in_progress'])
                ->count();
        }

        // Build arrays
        $xData = array_fill(1, 12, 0);
        $oData = array_fill(1, 12, 0);
        foreach ($newFindings as $month => $count) { $xData[(int)$month] = $count; }
        foreach ($resolvedFindings as $month => $count) { $oData[(int)$month] = $count; }

        return [
            'datasets' => [
                [
                    'label' => 'X — Temuan Baru',
                    'data' => array_values($xData),
                    'backgroundColor' => '#dc2626',
                    'borderColor' => '#dc2626',
                ],
                [
                    'label' => 'O — Tindak Lanjut (Resolved)',
                    'data' => array_values($oData),
                    'backgroundColor' => '#16a34a',
                    'borderColor' => '#16a34a',
                ],
                [
                    'label' => '△ — Belum Selesai',
                    'data' => array_values($unresolvedPerMonth),
                    'backgroundColor' => '#d97706',
                    'borderColor' => '#d97706',
                    'borderDash' => [5, 5],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
