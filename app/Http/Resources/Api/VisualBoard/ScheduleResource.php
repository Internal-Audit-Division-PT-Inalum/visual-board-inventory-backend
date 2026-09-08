<?php

namespace App\Http\Resources\Api\VisualBoard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period_month' => $this->period_month->format('Y-m'),
            'status' => $this->status,
            'approval_data' => $this->approval_data,
            'zone' => [
                'id' => $this->zone->id ?? null,
                'name' => $this->zone->name ?? null,
                'area' => $this->zone->area ?? null,
                'pic_utama' => $this->zone->picUtama->name ?? null,
            ],
            'records' => $this->whenLoaded('records', function () {
                return $this->records->map(function ($record) {
                    return [
                        'record_id' => $record->id,
                        'criteria_group' => $record->criteria->item_group ?? null,
                        'criteria_code' => $record->criteria->criteria_code ?? null,
                        'criteria_desc' => $record->criteria->description ?? null,
                        'days_data' => $record->days_data,
                    ];
                });
            }),
        ];
    }
}
