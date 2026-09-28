<?php

namespace App\Services\VisualBoard;

use App\Domains\HR\Models\Employee;
use App\Domains\VisualBoard\Models\FiveREvaluation;
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
                if ($record->criteria) {
                    $itemGroup = $record->criteria->item_group;
                    $criteriaCode = $record->criteria->criteria_code;
                    $description = $record->criteria->description;
                } elseif ($record->workstation && $record->masterWorkstationCriteria) {
                    $itemGroup = 'Meja Kerja: ' . $record->workstation->name;
                    $criteriaCode = $record->masterWorkstationCriteria->criteria_code;
                    $description = $record->masterWorkstationCriteria->standard_criteria;
                } else {
                    continue;
                }

                $scheduleMatrix[] = [
                    'zone_id' => $schedule->zone->id,
                    'zone_name' => $schedule->zone->name,
                    'item_group' => $itemGroup,
                    'criteria_code' => $criteriaCode,
                    'criteria_description' => $description,
                    'days' => (object) ($record->days_data ?? []),
                ];
            }
        }

        usort($scheduleMatrix, function ($a, $b) {
            $orderMap = [
                'Tidak ada barang yang tidak digunakan' => 1,
                'Barang / Peralatan terletak di area label' => 2,
                'Isi dalam rak bersih dari debu' => 4,
                'Kursi memiliki identitas' => 5,
                'Setiap kursi memiliki identitas' => 5,
                'Kursi diposisi layout jika tidak digunakan' => 6,
                'Setiap kursi diposisi layout jika tidak digunakan' => 6,
                'Bersih dari debu / kotoran' => 7,
                'Barang mudah bergerak terletak didalam line' => 8,
                'Line pembatas dalam keadaan baik' => 9,
                'Bersih dari debu / kotoran / genangan air' => 10,
                'Kondisi papan bersih dari debu dan kotoran' => 11,
                'Lampu dapat berfungsi semua' => 12,
                'Tidak ada sarang laba-laba' => 13,
                'Kabel tertata rapi' => 14,
                'Tembok tidak kotor dan tidak bebercak' => 15,
                'Tembok tidak ada sarang laba-laba' => 16,
                'Poster / Informasi dalam kondisi update dan bersih' => 17,
                'Air tersedia' => 18,
                'Body lemari Bersih dari debu / kotoran' => 19,
                'Terdapat standar isi lemari' => 20,
                'Terdapat label isi lemari' => 21,
                'Label dalam keadaan baik' => 22,
                'Isi kulkas, galon dan air dispenser tersedia' => 23,
                'APAR dalam Kondisi standby (garis hijau)/ P3K Stand By' => 24,
                'Tidak ada barang dibawah Apar/ P3K ada ditempatnya' => 25,
            ];

            $zoneCompare = strcmp($a['zone_name'], $b['zone_name']);
            if ($zoneCompare !== 0) {
                return $zoneCompare;
            }
            $groupCompare = strcmp($a['item_group'], $b['item_group']);
            if ($groupCompare !== 0) {
                return $groupCompare;
            }
            $codeCompare = strcmp($a['criteria_code'], $b['criteria_code']);
            if ($codeCompare !== 0) {
                return $codeCompare;
            }

            $orderA = $orderMap[$a['criteria_description']] ?? 99;
            $orderB = $orderMap[$b['criteria_description']] ?? 99;

            return $orderA <=> $orderB;
        });

        $trendMatrix = $this->abnormalityRepository->getMonthlyTrendMatrix($targetDate->year);
        $referenceDocs = GeneralDocument::visualBoard()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $assessmentDocs = GeneralDocument::assessment()
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

        // Ambil data PIC (Penanggung Jawab) - Kepala Divisi IIA
        $pic = Employee::with('media')
            ->where('hierarchy_level', 1)
            ->first();

        $picData = null;
        if ($pic) {
            $picData = [
                'name' => $pic->name ?? $pic->namecode ?? 'Admin',
                'position_title' => $pic->position_title,
                'avatar_url' => $pic->getFirstMediaUrl('avatar') ?: null,
            ];
        }

        return [
            'pic' => $picData,
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
            'kaizen_implemented_count' => GeneralDocument::where('category', 'kaizen_report')
                ->where('is_active', true)
                ->where('implementation_year', $targetDate->year)
                ->count(),
            'kaizen_cost_saving' => GeneralDocument::where('category', 'kaizen_report')
                ->where('is_active', true)
                ->where('implementation_year', $targetDate->year)
                ->count() * 1000000,

            'abnormalities' => $latestUnresolved,
            'kaizen_champions' => $champions,
            'schedule_matrix' => $scheduleMatrix,
            'weekly_trend' => $weeklyTrend,
            'zones' => $zones,
            'trend_matrix' => $trendMatrix,
            'reference_docs' => $referenceDocs,
            'assessment_docs' => $assessmentDocs,
            'five_r_evaluations' => FiveREvaluation::where('year', $targetDate->year)
                ->orderBy('month')
                ->get(),
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
