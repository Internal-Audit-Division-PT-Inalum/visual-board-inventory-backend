<?php

namespace App\Http\Controllers\Api\Portal;

use App\Domains\Portal\Models\Bulletin;
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
        $type = $request->input('type');
        $bulletins = $this->portalService->getKioskBulletins($limit, $type);

        return ApiResponse::success(
            BulletinResource::collection($bulletins),
            'Berhasil memuat pengumuman mading.'
        );
    }

    /**
     * Get Bulletin Document
     */
    public function kioskDocument(Bulletin $bulletin)
    {
        if (! $bulletin->document_url) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        $path = storage_path('app/public/' . $bulletin->document_url);

        if (! file_exists($path)) {
            abort(404, 'Dokumen fisik tidak ditemukan.');
        }

        // Return as response()->file which automatically adds CORS headers
        // By using this route (/bulletins/{id}/document), IDM usually ignores it
        // since the URL doesn't end in .pdf.
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Access-Control-Allow-Origin' => '*', // Force CORS allow all just to be safe
        ]);
    }
}
