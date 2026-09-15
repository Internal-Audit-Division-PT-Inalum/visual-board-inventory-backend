<?php

namespace App\Http\Controllers\Api\VisualBoard;

use App\Http\Controllers\Controller;
use App\Http\Requests\VisualBoard\StoreWorkstationRequest;
use App\Http\Resources\Api\VisualBoard\WorkstationResource;
use App\Repositories\Contracts\WorkstationRepositoryInterface;
use App\Services\VisualBoard\WorkstationService;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Visual Board Domain
 *
 * @subgroup Workstation / Meja (Admin)
 */
class WorkstationController extends Controller
{
    public function __construct(
        private WorkstationRepositoryInterface $repository,
        private WorkstationService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $workstations = $this->repository->getAllPaginated(
            $request->only(['search', 'zone_id']),
            $request->input('per_page', 15)
        );

        return WorkstationResource::collection($workstations);
    }

    public function store(StoreWorkstationRequest $request): JsonResponse
    {
        $workstation = $this->service->createWorkstation($request->validated());

        return ApiResponse::success(
            new WorkstationResource($workstation),
            'Meja berhasil didaftarkan.',
            [],
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $workstation = $this->repository->findWithItems($id);

        if (! $workstation) {
            return ApiResponse::error('Meja tidak ditemukan.', [], 404);
        }

        return ApiResponse::success(new WorkstationResource($workstation));
    }

    public function update(StoreWorkstationRequest $request, string $id): JsonResponse
    {
        $workstation = $this->repository->findById($id);

        if (! $workstation) {
            return ApiResponse::error('Meja tidak ditemukan.', [], 404);
        }

        $updated = $this->service->updateWorkstation($id, $request->validated());

        return ApiResponse::success(new WorkstationResource($updated), 'Meja diperbarui.');
    }

    public function destroy(string $id): JsonResponse
    {
        $workstation = $this->repository->findById($id);

        if (! $workstation) {
            return ApiResponse::error('Meja tidak ditemukan.', [], 404);
        }

        $this->service->deleteWorkstation($id);

        return ApiResponse::success(null, 'Meja dihapus.');
    }
}
