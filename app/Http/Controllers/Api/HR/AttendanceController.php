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
 * @subgroup Presensi
 *
 * Endpoint API untuk manajemen kehadiran pegawai.
 */
class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    /**
     * Scan Presensi Barcode
     *
     * Merekam kehadiran pegawai hari ini berdasarkan pemindaian barcode ID (namecode).
     * Akan mengembalikan error (422) jika pegawai tidak ditemukan atau sudah melakukan presensi hari ini.
     *
     * @bodyParam namecode string required Kode barcode / namecode pegawai. Example: K-12345
     */
    public function scan(ScanAttendanceRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->processScan($request->validated('namecode'));

        return ApiResponse::success(
            ['id' => $attendance->id, 'status' => $attendance->status, 'date' => $attendance->date],
            'Presensi berhasil dicatat.',
            [],
            201
        );
    }
}
