<?php

namespace App\Http\Resources\Api\VisualBoard;

use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Domains\VisualBoard\Models\ScheduleRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MonthlySchedule
 */
class ScheduleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period_month' => $this->period_month->format('Y-m'),
            'status' => $this->status,
            'approval_data' => $this->approval_data,
            'zone' => [
                'id' => $this->zone?->id,
                'name' => $this->zone?->name,
                'area' => $this->zone?->area,
                'pic_utama' => $this->zone?->picUtama?->name,
            ],
            'records' => $this->whenLoaded('scheduleRecords', function (): array {
                return $this->scheduleRecords->map(function (ScheduleRecord $record): array {
                    return [
                        'record_id' => $record->id,
                        'criteria_group' => $record->criteria?->item_group,
                        'criteria_code' => $record->criteria?->criteria_code,
                        'criteria_desc' => $record->criteria?->description,
                        'days_data' => $record->days_data,
                    ];
                })->values()->all();
            }),
        ];
    }
}
