<?php

namespace App\Http\Resources\Api\VisualBoard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisualBoardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'organization_structure' => $this->resource['organization_structure']->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'pic_utama' => $zone->picUtama ? $zone->picUtama->name : null,
                    'pic_pengganti' => $zone->picPengganti ? $zone->picPengganti->name : null,
                ];
            }),
            'abnormality_trend' => $this->resource['abnormality_trend'],
            'open_problems' => AbnormalityResource::collection($this->resource['open_problems']),
        ];
    }
}
