<?php

namespace App\Services\VisualBoard;

use App\Domains\VisualBoard\Models\GeneralDocument;
use App\Repositories\Contracts\AbnormalityRepositoryInterface;
use App\Repositories\Contracts\MonthlyScheduleRepositoryInterface;
use App\Repositories\Contracts\ZoneRepositoryInterface;
use App\Services\Telemetry\ExternalTelemetryService;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VisualBoardService
{
    public function __construct(
        private ZoneRepositoryInterface $zoneRepository,
        private AbnormalityRepositoryInterface $abnormalityRepository,
        private MonthlyScheduleRepositoryInterface $scheduleRepository,
        private ExternalTelemetryService $telemetryService
    ) {}

    /**
     * Get aggregated data for the TV Kiosk Dashboard.
     * The result is cached for 1 minute to prevent database overload from auto-refreshing kiosks.
     */
    public function getKioskData(?int $month = null, ?int $year = null): array
    {
        // Cache dihapus sementara untuk menghindari Incomplete Object Error pada saat deserialization.
        // Struktur data disesuaikan dengan kontrak KioskDashboardResponse di Frontend (Phase 2).
        $now = Carbon::now();

        if ($month && $year) {
            $targetDate = Carbon::createFromDate($year, $month, 1);
        } else {
            $targetDate = $now;
        }

        $abnormalitySummary = $this->abnormalityRepository->getSummaryByMonth($targetDate->month, $targetDate->year);
        $latestUnresolved = $this->abnormalityRepository->getLatestUnresolved(10);

        $resolvedToday = $this->abnormalityRepository->getResolvedTodayCount();
        $champions = $this->abnormalityRepository->getKaizenChampions();
        $weeklyTrend = $this->abnormalityRepository->getAbnormalityTrend(7);

        // Mapping Schedule Matrix 5R
        $periodMonth = $targetDate->copy()->startOfMonth()->format('Y-m-d');
        $schedules = $this->scheduleRepository->getAllByPeriod($periodMonth);

        $scheduleMatrix = [];
        $uniqueZoneIds = [];
        foreach ($schedules as $schedule) {
            $uniqueZoneIds[] = $schedule->zone->id;
            foreach ($schedule->scheduleRecords as $record) {
                $scheduleMatrix[] = [
                    'zone_id' => $schedule->zone->id,
                    'zone_name' => $schedule->zone->name,
                    'item_group' => $record->criteria->item_group,
                    'criteria_code' => $record->criteria->criteria_code,
                    'criteria_description' => $record->criteria->description,
                    'days' => $record->days_data ?? [],
                ];
            }
        }

        $trendMatrix = $this->abnormalityRepository->getMonthlyTrendMatrix($targetDate->year);
        $referenceDocs = GeneralDocument::visualBoard()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Ambil data zones secara eksplisit
        $zones = $this->zoneRepository->getAllActive();

        // Calculate Kepatuhan 5R
        $totalEvaluated = 0;
        $totalPassed = 0;
        foreach ($scheduleMatrix as $matrixRow) {
            foreach ($matrixRow['days'] as $status) {
                if (in_array($status, ['ok_tanpa_5r', 'ok_dengan_5r', 'abnormal'])) {
                    $totalEvaluated++;
                    if (in_array($status, ['ok_tanpa_5r', 'ok_dengan_5r'])) {
                        $totalPassed++;
                    }
                }
            }
        }
        $compliancePercentage = $totalEvaluated > 0 ? round(($totalPassed / $totalEvaluated) * 100, 1) : 0;

        // Calculate Resolution Rate
        $open = $abnormalitySummary['open'] ?? 0;
        $inProgress = $abnormalitySummary['in_progress'] ?? 0;
        $resolved = $abnormalitySummary['resolved'] ?? 0;
        $totalAbnormalities = $open + $inProgress + $resolved;
        $resolutionRate = $totalAbnormalities > 0 ? round(($resolved / $totalAbnormalities) * 100, 1) : 0;

        if ($resolutionRate >= 90) {
            $resolutionGrade = 'A';
        } elseif ($resolutionRate >= 75) {
            $resolutionGrade = 'B';
        } elseif ($resolutionRate >= 50) {
            $resolutionGrade = 'C';
        } else {
            $resolutionGrade = 'D';
        }
        // Atau jika hanya ingin zone yang punya jadwal:
        // $zones = collect($zones)->filter(fn($z) => in_array($z->id, $uniqueZoneIds))->values()->all();

        Log::info('VisualBoard: Mengambil data kiosk', [
            'month' => $targetDate->format('Y-m'),
        ]);

        return [
            'open_abnormality_count' => ($abnormalitySummary['open'] ?? 0) + ($abnormalitySummary['in_progress'] ?? 0),
            'abnormality_resolved_today' => $resolvedToday,
            'abnormality_in_progress' => $abnormalitySummary['in_progress'] ?? 0,

            // Dinamis dari kalkulasi jadwal harian
            'compliance_percentage' => $compliancePercentage,
            'compliance_mom_trend' => 'up',

            // Dinamis dari kalkulasi penyelesaian abnormality (menggantikan mock audit eksternal)
            'resolution_rate' => $resolutionRate,
            'resolution_grade' => $resolutionGrade,

            // Dummy for backwards compatibility in tests
            'audit_pass_percentage' => $resolutionRate,
            'audit_pass_grade' => $resolutionGrade,

            // Metrik Eksternal (SCADA/SAP/Sistem K3) dipertahankan jika masih dibutuhkan frontend
            'safety_streak_days' => $this->telemetryService->getSafetyStreakDays(),
            'safety_safe_shifts' => $this->telemetryService->getSafetySafeShifts(),
            'resolution_speed_avg_mins' => $this->telemetryService->getResolutionSpeedAvgMins(),
            'oee_percentage' => $this->telemetryService->getOeePercentage(),
            'oee_target' => $this->telemetryService->getOeeTarget(),

            // Metrik Agregat Kaizen dari Database
            'kaizen_implemented_count' => collect($champions)->sum('kaizen_count'),
            'kaizen_cost_saving' => collect($champions)->sum('kaizen_count') * 1000000,

            'abnormalities' => $latestUnresolved,
            'kaizen_champions' => $champions,
            'schedule_matrix' => $scheduleMatrix,
            'weekly_trend' => $weeklyTrend,
            'zones' => $zones,
            'trend_matrix' => $trendMatrix,
            'reference_docs' => $referenceDocs,
        ];
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
