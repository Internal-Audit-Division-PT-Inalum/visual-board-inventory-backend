<?php

namespace App\Http\Controllers\Api\VisualBoard;

use App\Http\Controllers\Controller;
use App\Http\Requests\VisualBoard\StoreAbnormalityRequest;
use App\Http\Requests\VisualBoard\UpdateAbnormalityProgressRequest;
use App\Http\Resources\Api\VisualBoard\AbnormalityResource;
use App\Repositories\Contracts\AbnormalityRepositoryInterface;
use App\Services\VisualBoard\AbnormalityService;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Visual Board Domain
 *
 * @subgroup Manajemen Abnormality
 *
 * Endpoint untuk mencatat dan mengelola masalah (abnormality) dari inspeksi 5R harian.
 */
class AbnormalityController extends Controller
{
    public function __construct(
        protected AbnormalityRepositoryInterface $repository,
        protected AbnormalityService $service
    ) {}

    /**
     * Display a listing of the abnormalities.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['zone_id', 'status', 'is_kaizen']);
        $perPage = $request->input('per_page', 15);

        $abnormalities = $this->repository->getAllPaginated($filters, $perPage);

        return AbnormalityResource::collection($abnormalities);
    }

    /**
     * Buat Abnormality Baru
     *
     * Endpoint ini mencatat masalah baru yang ditemukan di lapangan.
     */
    public function store(StoreAbnormalityRequest $request): JsonResponse
    {
        $abnormality = $this->service->createAbnormality($request->validated());

        return ApiResponse::success(
            new AbnormalityResource($abnormality),
            'Abnormality created successfully',
            [],
            201
        );
    }

    /**
     * Detail Abnormality
     *
     * Mengambil detail lengkap suatu masalah beserta progressnya.
     *
     * @urlParam id string required ULID dari abnormality.
     */
    public function show(string $id): JsonResponse
    {
        $abnormality = $this->repository->findById($id);

        if (! $abnormality) {
            return ApiResponse::error('Abnormality not found', 404);
        }

        return ApiResponse::success(new AbnormalityResource($abnormality));
    }

    /**
     * Update Progress Abnormality
     *
     * Menyimpan progres perbaikan (0-100%) dan aktual dari penanggulangan masalah.
     * Jika persentase 100, status otomatis berubah menjadi 'resolved'.
     *
     * @urlParam id string required ULID dari abnormality.
     */
    public function updateProgress(UpdateAbnormalityProgressRequest $request, string $id): JsonResponse
    {
        $abnormality = $this->repository->findById($id);

        if (! $abnormality) {
            return ApiResponse::error('Abnormality not found', 404);
        }

        $updatedAbnormality = $this->service->updateProgress(
            $id,
            $request->validated('progress_percentage'),
            $request->validated('countermeasure_actual'),
            $request->validated('pic_id')
        );

        return ApiResponse::success(
            new AbnormalityResource($updatedAbnormality),
            'Progress updated successfully'
        );
    }

    /**
     * Hapus Abnormality
     *
     * Menghapus catatan abnormality (menggunakan soft deletes).
     *
     * @urlParam id string required ULID dari abnormality.
     */
    public function destroy(string $id): JsonResponse
    {
        $abnormality = $this->repository->findById($id);

        if (! $abnormality) {
            return ApiResponse::error('Abnormality not found', 404);
        }

        $this->service->deleteAbnormality($id);

        return ApiResponse::success(null, 'Abnormality deleted successfully');
    }
}
