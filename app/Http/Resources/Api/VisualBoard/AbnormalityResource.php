<?php

namespace App\Http\Resources\Api\VisualBoard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbnormalityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'zone_id' => $this->zone_id,
            'monthly_schedule_id' => $this->monthly_schedule_id,
            'inspection_criteria_id' => $this->inspection_criteria_id,
            'date_found' => $this->date_found ? $this->date_found->format('Y-m-d') : null,
            'problem_description' => $this->problem_description,
            'countermeasure_plan' => $this->countermeasure_plan,
            'countermeasure_actual' => $this->countermeasure_actual,
            'pic_id' => $this->pic_id,
            'status' => $this->status,
            'progress_percentage' => $this->progress_percentage,
            'is_kaizen' => $this->is_kaizen,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'zone' => $this->whenLoaded('zone', function () {
                return [
                    'id' => $this->zone->id,
                    'name' => $this->zone->name,
                ];
            }),
            'pic' => $this->whenLoaded('pic', function () {
                return [
                    'id' => $this->pic->id,
                    'name' => $this->pic->name,
                ];
            }),
            'criteria' => $this->whenLoaded('criteria', function () {
                return [
                    'id' => $this->criteria->id,
                    'criteria_code' => $this->criteria->criteria_code,
                    'description' => $this->criteria->description,
                ];
            }),
        ];
    }
}
