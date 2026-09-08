<?php

namespace App\Http\Controllers\Api\VisualBoard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\VisualBoard\VisualBoardResource;
use App\Services\VisualBoard\VisualBoardService;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @group Visual Board Domain
 * @subgroup Kiosk Dashboard
 *
 * Endpoint API agregasi data untuk konsumsi Read-Only pada layar TV Kiosk (Modul 1.3).
 */
class VisualBoardController extends Controller
{
    public function __construct(
        private VisualBoardService $visualBoardService
    ) {}

    /**
     * Data Kiosk Dashboard
     * 
     * Endpoint ini mengambil agregasi struktur organisasi, tren masalah bulanan,
     * dan daftar masalah yang belum terselesaikan. Hasil dari kueri ini di-cache
     * secara otomatis selama 1 menit.
     */
    public function index(): JsonResponse
    {
        $data = $this->visualBoardService->getKioskData();

        return ApiResponse::success(
            new VisualBoardResource($data),
            'Berhasil memuat data Kiosk Dashboard.'
        );
    }
}
