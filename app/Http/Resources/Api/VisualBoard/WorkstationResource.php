<?php

namespace App\Http\Resources\Api\VisualBoard;

use App\Domains\Inventory\Models\Item;
use App\Domains\VisualBoard\Models\MasterWorkstationCriteria;
use App\Domains\VisualBoard\Models\Workstation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Workstation
 */
class WorkstationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'standard_image_url' => $this->hasMedia('standard_images')
                ? $this->getFirstMediaUrl('standard_images')
                : null,
            'zone' => $this->whenLoaded('zone', fn () => $this->zone ? [
                'id' => $this->zone->id,
                'name' => $this->zone->name,
            ] : null),
            'employee' => $this->whenLoaded('employee', fn () => $this->employee ? [
                'id' => $this->employee->id,
                'namecode' => $this->employee->namecode,
                'name' => $this->employee->name ?? ($this->employee->user ? $this->employee->user->name : $this->employee->namecode),
                'hierarchy_level' => $this->employee->hierarchy_level,
                'avatar_url' => $this->employee->getFirstMediaUrl('avatar') ?: null,
            ] : null),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function (Item $item): array {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'sku' => $item->sku,
                        'standard_quantity' => $item->getAttribute('pivot')?->getAttribute('standard_quantity'),
                    ];
                });
            }),
            'master_criterias' => $this->when(
                $this->resource->hasAttribute('master_criterias') && $this->master_criterias !== null,
                function () {
                    return $this->master_criterias?->map(function (MasterWorkstationCriteria $criteria): array {
                        return [
                            'id' => $criteria->id,
                            'item_group' => $criteria->item_group,
                            'criteria_code' => $criteria->criteria_code,
                            'standard_criteria' => $criteria->standard_criteria,
                        ];
                    });
                }
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
