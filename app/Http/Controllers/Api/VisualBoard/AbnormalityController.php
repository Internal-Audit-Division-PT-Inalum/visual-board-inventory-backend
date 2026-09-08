<?php

namespace App\Http\Controllers\Api\VisualBoard;

use App\Http\Controllers\Controller;
use App\Http\Requests\VisualBoard\StoreAbnormalityRequest;
use App\Http\Requests\VisualBoard\UpdateAbnormalityProgressRequest;
use App\Http\Resources\Api\VisualBoard\AbnormalityResource;
use App\Repositories\Contracts\AbnormalityRepositoryInterface;
use App\Services\VisualBoard\AbnormalityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * Store a newly created abnormality.
     */
    public function store(StoreAbnormalityRequest $request): JsonResponse
    {
        $abnormality = $this->service->createAbnormality($request->validated());

        return response()->json([
            'message' => 'Abnormality created successfully',
            'data' => new AbnormalityResource($abnormality),
        ], 201);
    }

    /**
     * Display the specified abnormality.
     */
    public function show(string $id): JsonResponse
    {
        $abnormality = $this->repository->findById($id);

        if (! $abnormality) {
            return response()->json(['message' => 'Abnormality not found'], 404);
        }

        return response()->json([
            'data' => new AbnormalityResource($abnormality),
        ]);
    }

    /**
     * Update progress of the specified abnormality.
     */
    public function updateProgress(UpdateAbnormalityProgressRequest $request, string $id): JsonResponse
    {
        $abnormality = $this->repository->findById($id);

        if (! $abnormality) {
            return response()->json(['message' => 'Abnormality not found'], 404);
        }

        $updatedAbnormality = $this->service->updateProgress(
            $id,
            $request->validated('progress_percentage'),
            $request->validated('countermeasure_actual'),
            $request->validated('pic_id')
        );

        return response()->json([
            'message' => 'Progress updated successfully',
            'data' => new AbnormalityResource($updatedAbnormality),
        ]);
    }

    /**
     * Remove the specified abnormality.
     */
    public function destroy(string $id): JsonResponse
    {
        $abnormality = $this->repository->findById($id);

        if (! $abnormality) {
            return response()->json(['message' => 'Abnormality not found'], 404);
        }

        $this->service->deleteAbnormality($id);

        return response()->json([
            'message' => 'Abnormality deleted successfully',
        ]);
    }
}
