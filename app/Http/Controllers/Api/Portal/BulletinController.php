<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Portal\BulletinResource;
use App\Services\Portal\PortalService;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Portal Domain
 *
 * @subgroup Mading Kiosk (Tanpa Auth)
 *
 * Endpoint API Read-Only untuk mengambil pengumuman mading pada TV Kiosk.
 */
class BulletinController extends Controller
{
    public function __construct(
        private PortalService $portalService
    ) {}

    /**
     * Daftar Pengumuman Aktif
     *
     * Mengambil daftar pengumuman (bulletins) yang berstatus aktif dan sudah dipublikasi.
     * Endpoint ini ditujukan untuk layar TV dan tidak memerlukan login.
     * Hasilnya otomatis di-cache selama 5 menit.
     *
     * @queryParam limit integer Batas jumlah pengumuman yang dikembalikan. Default: 10. Example: 5
     */
    public function kioskIndex(Request $request): JsonResponse
    {
        $limit = (int) $request->input('limit', 10);
        $bulletins = $this->portalService->getKioskBulletins($limit);

        return ApiResponse::success(
            BulletinResource::collection($bulletins),
            'Berhasil memuat pengumuman mading.'
        );
    }
}
