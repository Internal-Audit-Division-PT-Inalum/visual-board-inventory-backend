<?php

namespace App\Services\VisualBoard;

use App\Repositories\Contracts\AbnormalityRepositoryInterface;
use App\Repositories\Contracts\ZoneRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class VisualBoardService
{
    public function __construct(
        private ZoneRepositoryInterface $zoneRepository,
        private AbnormalityRepositoryInterface $abnormalityRepository
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
}
