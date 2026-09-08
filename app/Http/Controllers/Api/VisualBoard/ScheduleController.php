<?php

namespace App\Http\Controllers\Api\VisualBoard;

use App\Http\Controllers\Controller;
use App\Http\Requests\VisualBoard\UpdateDailyStatusRequest;
use App\Http\Resources\Api\VisualBoard\ScheduleResource;
use App\Repositories\Contracts\MonthlyScheduleRepositoryInterface;
use App\Services\VisualBoard\ScheduleService;
use App\Shared\Responses\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    public function __construct(
        private MonthlyScheduleRepositoryInterface $scheduleRepository,
        private ScheduleService $scheduleService
    ) {}

    public function show(string $id): JsonResponse
    {
        $schedule = $this->scheduleRepository->getScheduleWithRecords($id);

        if (! $schedule) {
            return ApiResponse::error('Jadwal bulanan tidak ditemukan.', [], 404);
        }

        return ApiResponse::success(
            new ScheduleResource($schedule),
            'Berhasil memuat detail jadwal.'
        );
    }

    public function updateDay(UpdateDailyStatusRequest $request, string $recordId): JsonResponse
    {
        try {
            $this->scheduleService->updateDailyStatus(
                $recordId,
                $request->validated('day'),
                $request->validated('status')
            );

            return ApiResponse::success(null, 'Status harian 5R berhasil diperbarui.');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }
}
