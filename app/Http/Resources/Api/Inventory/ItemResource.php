<?php

namespace App\Http\Resources\Api\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'type' => $this->type,
            'unit' => $this->unit,
            'current_stock' => $this->current_stock,
            'minimum_stock' => $this->minimum_stock,
            'is_low_stock' => $this->current_stock <= $this->minimum_stock,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'location' => new LocationResource($this->whenLoaded('location')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
