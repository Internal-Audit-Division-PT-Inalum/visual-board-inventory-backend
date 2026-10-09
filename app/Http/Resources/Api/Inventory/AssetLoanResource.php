<?php

namespace App\Http\Resources\Api\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetLoanResource extends JsonResource
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
            'item_id' => $this->item_id,
            'item_name' => $this->whenLoaded('item', fn () => $this->item->name),
            'item_sku' => $this->whenLoaded('item', fn () => $this->item->sku),
            'image_thumb_url' => $this->whenLoaded('item', fn () => $this->item->getFirstMediaUrl('item_images', 'thumb') ?: null),
            'quantity' => $this->quantity,
            'returned_quantity' => $this->returned_quantity,
            'outstanding_quantity' => $this->quantity - $this->returned_quantity,
            'borrowed_at' => $this->borrowed_at,
            'fully_returned_at' => $this->fully_returned_at,
            'status' => $this->fully_returned_at ? 'returned' : 'borrowed',
        ];
    }
}
