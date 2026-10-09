<?php

namespace App\Http\Resources\Api\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LedgerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'stock_before' => $this->stock_before,
            'stock_after' => $this->stock_after,
            'notes' => $this->notes,
            'reference_number' => $this->reference_number,
            'client_uuid' => $this->client_uuid,
            'occurred_at' => $this->occurred_at,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'employee_name' => $this->user?->employee?->name,
            ],
            'item' => $this->whenLoaded('item', fn () => [
                'id' => $this->item->id,
                'current_stock' => $this->item->current_stock,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
