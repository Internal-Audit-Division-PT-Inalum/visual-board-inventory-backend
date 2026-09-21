<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\ScanAttendanceRequest;
use App\Services\HR\AttendanceService;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @group HR Domain
 *
 * @subgroup Presensi Kiosk (Tanpa Auth)
 *
 * Endpoint API Read-Only untuk mengambil ringkasan presensi harian pada TV Kiosk.
 */
class KioskAttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    /**
     * Ringkasan Kehadiran Hari Ini
     *
     * Mengambil rekap presensi hari ini (jumlah hadir, absen, cuti) beserta daftar pegawai yang tidak tersedia.
     * Endpoint ini ditujukan untuk layar TV dan tidak memerlukan login.
     * Hasilnya otomatis di-cache selama 5 menit.
     */
    public function summary(): JsonResponse
    {
        $summary = $this->attendanceService->getKioskSummary();

        return ApiResponse::success(
            $summary,
            'Berhasil memuat ringkasan kehadiran hari ini.'
        );
    }

    /**
     * Scan Presensi Kiosk
     */
    public function scan(ScanAttendanceRequest $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->processScan($request->validated('namecode'));

            return ApiResponse::success(
                ['id' => $attendance->id, 'status' => $attendance->status, 'date' => $attendance->date],
                'Presensi berhasil dicatat.',
                [],
                201
            );
        } catch (\DomainException $e) {
            return ApiResponse::error(
                $e->getMessage(),
                [],
                422
            );
        }
    }
}
