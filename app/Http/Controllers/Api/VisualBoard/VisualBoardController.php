<?php

namespace App\Http\Controllers\Api\VisualBoard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\VisualBoard\VisualBoardResource;
use App\Services\VisualBoard\VisualBoardService;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class VisualBoardController extends Controller
{
    public function __construct(
        private VisualBoardService $visualBoardService
    ) {}

    /**
     * Get aggregated data for the TV Kiosk Dashboard.
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
