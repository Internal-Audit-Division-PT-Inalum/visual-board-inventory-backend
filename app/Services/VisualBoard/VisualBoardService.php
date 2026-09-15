<?php

namespace App\Services\VisualBoard;

use App\Repositories\Contracts\AbnormalityRepositoryInterface;
use App\Repositories\Contracts\MonthlyScheduleRepositoryInterface;
use App\Repositories\Contracts\ZoneRepositoryInterface;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VisualBoardService
{
    public function __construct(
        private ZoneRepositoryInterface $zoneRepository,
        private AbnormalityRepositoryInterface $abnormalityRepository,
        private MonthlyScheduleRepositoryInterface $scheduleRepository
    ) {}

    /**
     * Get aggregated data for the TV Kiosk Dashboard.
     * The result is cached for 1 minute to prevent database overload from auto-refreshing kiosks.
     */
    public function getKioskData(): array
    {
        return Cache::remember('visual_board.kiosk_data', 60, function () {
            $now = Carbon::now();

            $zones = $this->zoneRepository->getAllWithPics();
            $abnormalitySummary = $this->abnormalityRepository->getSummaryByMonth($now->month, $now->year);
            $latestUnresolved = $this->abnormalityRepository->getLatestUnresolved(5);

            Log::info('VisualBoard: kiosk data cache miss — rebuilding', [
                'month' => $now->format('Y-m'),
                'zones_count' => $zones->count(),
            ]);

            return [
                'organization_structure' => $zones,
                'abnormality_trend' => [
                    'month' => $now->format('F Y'),
                    'summary' => $abnormalitySummary,
                ],
                'open_problems' => $latestUnresolved,
            ];
        });
    }

    /**
     * Get Check Sheet (Schedule Records) for a specific Zone for today.
     * Digunakan oleh endpoint Barcode Scanner.
     */
    public function getZoneTodayCheckSheet(string $zonaId): array
    {
        $zone = $this->zoneRepository->findById($zonaId);

        if (! $zone) {
            throw new DomainException('Zona tidak ditemukan.');
        }

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');

        $schedule = $this->scheduleRepository->findByZoneAndPeriod($zonaId, $startOfMonth);

        if (! $schedule) {
            throw new DomainException("Jadwal 5R untuk Zona {$zone->name} pada bulan ini belum dibuat.");
        }

        // Ambil data dengan records beserta kriteria inspeksinya
        $scheduleWithRecords = $this->scheduleRepository->getScheduleWithRecords($schedule->id);

        return [
            'zone' => $zone,
            'today' => $now->format('Y-m-d'),
            'day_of_month' => $now->day,
            'schedule' => $scheduleWithRecords,
        ];
    }
}
