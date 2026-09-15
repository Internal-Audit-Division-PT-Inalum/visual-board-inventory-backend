<?php

namespace App\Http\Controllers\Api\VisualBoard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\VisualBoard\WorkstationResource;
use App\Services\VisualBoard\WorkstationService;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @group Visual Board Domain
 *
 * @subgroup Kiosk Scanner (Tanpa Auth)
 */
class KioskWorkstationController extends Controller
{
    public function __construct(
        private WorkstationService $service
    ) {}

    /**
     * Data Standar Meja (Workstation)
     *
     * Digunakan saat pekerja melakukan scan barcode yang tertempel di meja.
     * Mengembalikan data meja, foto standar 5R, serta daftar ATK yang seharusnya ada.
     */
    public function show(string $id): JsonResponse
    {
        $workstation = $this->service->getKioskWorkstationData($id);

        if (! $workstation) {
            return ApiResponse::error('Data Meja tidak ditemukan.', [], 404);
        }

        return ApiResponse::success(
            new WorkstationResource($workstation),
            'Berhasil memuat standar meja.'
        );
    }
}
