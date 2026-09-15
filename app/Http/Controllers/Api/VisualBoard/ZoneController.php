<?php

namespace App\Http\Controllers\Api\VisualBoard;

use App\Http\Controllers\Controller;
use App\Services\VisualBoard\VisualBoardService;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @group Visual Board Domain
 *
 * @subgroup Pemindaian Zona (Scanner)
 *
 * Endpoint API untuk mengambil ceklis harian berdasarkan pemindaian Barcode / QR Code Zona.
 */
class ZoneController extends Controller
{
    public function __construct(
        private VisualBoardService $visualBoardService
    ) {}

    /**
     * Scan Barcode Zona
     *
     * Mengambil data Check Sheet (Jadwal + Kriteria Inspeksi) hari ini untuk suatu Zona tertentu.
     * Akan mengembalikan error (422/404) jika zona tidak ditemukan atau jadwal bulan ini belum dibuat.
     *
     * @urlParam zonaId string required ULID (barcode) dari zona. Example: 01H...
     */
    public function scan(string $zonaId): JsonResponse
    {
        $data = $this->visualBoardService->getZoneTodayCheckSheet($zonaId);

        return ApiResponse::success(
            $data,
            'Berhasil memuat Ceklis 5R untuk hari ini.'
        );
    }
}
