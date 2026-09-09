<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreLocationRequest;
use App\Http\Requests\Inventory\UpdateLocationRequest;
use App\Http\Resources\Api\Inventory\LocationResource;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @group Inventory Domain
 *
 * @subgroup Manajemen Lokasi
 *
 * Endpoint CRUD untuk lokasi penyimpanan barang (gudang, lemari, rak).
 */
class LocationController extends Controller
{
    public function __construct(
        private LocationRepositoryInterface $repository
    ) {}

    /**
     * Daftar Semua Lokasi
     *
     * Mengambil seluruh lokasi penyimpanan beserta jumlah item di dalamnya.
     */
    public function index(): JsonResponse
    {
        $locations = $this->repository->getAll();

        return ApiResponse::success(
            LocationResource::collection($locations),
            'Berhasil memuat daftar lokasi.'
        );
    }

    /**
     * Detail Lokasi
     *
     * @urlParam location string required ULID dari lokasi.
     */
    public function show(string $location): JsonResponse
    {
        $data = $this->repository->findById($location);

        if (! $data) {
            return ApiResponse::error('Lokasi tidak ditemukan.', [], 404);
        }

        return ApiResponse::success(
            new LocationResource($data),
            'Berhasil memuat detail lokasi.'
        );
    }

    /**
     * Buat Lokasi Baru
     */
    public function store(StoreLocationRequest $request): JsonResponse
    {
        $location = $this->repository->create($request->validated());

        return ApiResponse::success(
            new LocationResource($location),
            'Lokasi berhasil dibuat.',
            [],
            201
        );
    }

    /**
     * Update Lokasi
     *
     * @urlParam location string required ULID dari lokasi.
     */
    public function update(UpdateLocationRequest $request, string $location): JsonResponse
    {
        $data = $this->repository->findById($location);

        if (! $data) {
            return ApiResponse::error('Lokasi tidak ditemukan.', [], 404);
        }

        $updated = $this->repository->update($location, $request->validated());

        return ApiResponse::success(
            new LocationResource($updated),
            'Lokasi berhasil diperbarui.'
        );
    }

    /**
     * Hapus Lokasi
     *
     * @urlParam location string required ULID dari lokasi.
     */
    public function destroy(string $location): JsonResponse
    {
        $data = $this->repository->findById($location);

        if (! $data) {
            return ApiResponse::error('Lokasi tidak ditemukan.', [], 404);
        }

        $this->repository->delete($location);

        return ApiResponse::success(null, 'Lokasi berhasil dihapus.');
    }
}
