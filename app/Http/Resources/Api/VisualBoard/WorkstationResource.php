<?php

namespace App\Http\Resources\Api\VisualBoard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkstationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'standard_image_url' => $this->getFirstMediaUrl('standard_images') ? asset($this->getFirstMediaUrl('standard_images')) : null,
            'zone' => $this->whenLoaded('zone', fn () => [
                'id' => $this->zone->id,
                'name' => $this->zone->name,
            ]),
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee->id,
                'namecode' => $this->employee->namecode,
                'name' => $this->employee->name ?? $this->employee->user?->name ?? $this->employee->namecode,
                'avatar_url' => $this->employee->getFirstMediaUrl('avatar') ? asset($this->employee->getFirstMediaUrl('avatar')) : null,
            ]),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'sku' => $item->sku,
                        'standard_quantity' => $item->pivot->standard_quantity,
                    ];
                });
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
