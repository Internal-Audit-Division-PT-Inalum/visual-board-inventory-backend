<?php

namespace App\Services\VisualBoard;

use App\Domains\VisualBoard\Models\ScheduleRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScheduleService
{
    /**
     * Valid statuses sesuai kontrak GEMINI.md §5A (days_data).
     */
    private const VALID_STATUSES = ['rencana', 'ok_tanpa_5r', 'ok_dengan_5r', 'abnormal'];

    public function updateDailyStatus(string $recordId, int $day, string $status): bool
    {
        if (! in_array($status, self::VALID_STATUSES)) {
            throw new InvalidArgumentException(
                "Status 5R tidak valid: '{$status}'. Status yang diperbolehkan: " . implode(', ', self::VALID_STATUSES)
            );
        }

        if ($day < 1 || $day > 31) {
            throw new InvalidArgumentException('Tanggal harus berada di rentang 1-31.');
        }

        return DB::transaction(function () use ($recordId, $day, $status) {
            $record = ScheduleRecord::where('id', $recordId)->lockForUpdate()->first();

            if (! $record) {
                throw new NotFoundHttpException('Data inspeksi tidak ditemukan.');
            }

            $currentData = $record->days_data ?? [];
            $currentData[(string) $day] = $status;

            $result = $record->update(['days_data' => $currentData]);

            Log::info('ScheduleRecord: daily status updated', [
                'record_id' => $recordId,
                'day' => $day,
                'status' => $status,
                'user_id' => auth()->id(),
            ]);

            return $result;
        });
    }
}

