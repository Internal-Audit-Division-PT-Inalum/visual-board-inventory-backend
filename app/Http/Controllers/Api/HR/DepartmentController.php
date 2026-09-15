<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreDepartmentRequest;
use App\Http\Resources\Api\HR\DepartmentResource;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Services\HR\OrganizationService;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group HR Domain
 *
 * @subgroup Departemen (Admin)
 */
class DepartmentController extends Controller
{
    public function __construct(
        private DepartmentRepositoryInterface $repository,
        private OrganizationService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $departments = $this->repository->getAllPaginated(
            $request->only(['search', 'is_active']),
            $request->input('per_page', 15)
        );

        return DepartmentResource::collection($departments);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->service->createDepartment($request->validated());

        return ApiResponse::success(
            new DepartmentResource($department),
            'Departemen berhasil ditambahkan.',
            [],
            201
        );
    }

    public function show(string $id): JsonResponse
    {
        $department = $this->repository->findById($id);

        if (! $department) {
            return ApiResponse::error('Departemen tidak ditemukan.', [], 404);
        }

        return ApiResponse::success(new DepartmentResource($department));
    }

    public function update(StoreDepartmentRequest $request, string $id): JsonResponse
    {
        $department = $this->repository->findById($id);

        if (! $department) {
            return ApiResponse::error('Departemen tidak ditemukan.', [], 404);
        }

        $updated = $this->service->updateDepartment($id, $request->validated());

        return ApiResponse::success(new DepartmentResource($updated), 'Departemen diperbarui.');
    }

    public function destroy(string $id): JsonResponse
    {
        $department = $this->repository->findById($id);

        if (! $department) {
            return ApiResponse::error('Departemen tidak ditemukan.', [], 404);
        }

        $this->service->deleteDepartment($id);

        return ApiResponse::success(null, 'Departemen dihapus.');
    }
}
