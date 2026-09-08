<?php

namespace App\Services\VisualBoard;

use App\Domains\VisualBoard\Models\ScheduleRecord;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScheduleService
{
    public function updateDailyStatus(string $recordId, int $day, string $status): bool
    {
        $validStatuses = ['rencana', 'ok', 'ok_5r', 'abnormal', 'libur'];

        if (! in_array($status, $validStatuses)) {
            throw new InvalidArgumentException('Status 5R tidak valid.');
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

            return $record->update(['days_data' => $currentData]);
        });
    }
}
