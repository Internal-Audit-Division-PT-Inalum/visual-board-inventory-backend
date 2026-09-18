<?php

namespace App\Services\Telemetry;

/**
 * Service mock untuk metrik eksternal (SCADA/SAP/Sistem K3)
 * Digunakan sementara sebelum ada integrasi sistem sebenarnya.
 */
class ExternalTelemetryService
{
    /**
     * Dapatkan persentase OEE (Overall Equipment Effectiveness)
     */
    public function getOeePercentage(): float
    {
        return 89.4;
    }

    /**
     * Dapatkan target persentase OEE
     */
    public function getOeeTarget(): float
    {
        return 85.0;
    }

    /**
     * Dapatkan rekor hari tanpa LTI (Lost Time Injury)
     */
    public function getSafetyStreakDays(): int
    {
        return 120;
    }

    /**
     * Dapatkan jumlah shift aman
     */
    public function getSafetySafeShifts(): int
    {
        return 360;
    }

    /**
     * Dapatkan rata-rata kecepatan penyelesaian abnormality (menit)
     */
    public function getResolutionSpeedAvgMins(): int
    {
        return 42;
    }

    /**
     * Dapatkan persentase kelulusan audit 5R
     */
    public function getAuditPassPercentage(): float
    {
        return 98.4;
    }

    /**
     * Dapatkan grade audit 5R
     */
    public function getAuditPassGrade(): string
    {
        return 'Grade A';
    }

    /**
     * Dapatkan estimasi total penghematan biaya dari Kaizen (Rupiah)
     */
    public function getKaizenCostSaving(): string
    {
        return 'Rp 28.5M';
    }
}
