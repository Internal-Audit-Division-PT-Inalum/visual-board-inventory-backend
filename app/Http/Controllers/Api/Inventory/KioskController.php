<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use App\Http\Controllers\Controller;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KioskController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
    ) {}

    public function locations(Request $request): JsonResponse
    {
        $data = Cache::remember('inventory:kiosk:locations', now()->addMinutes(5), function (): array {
            $locations = Location::with(['items' => function ($query) {
                $query->where('is_active', true)->with('media');
            }])->get();

            $assetIds = [];
            foreach ($locations as $location) {
                foreach ($location->items as $item) {
                    if ($item->type === 'asset') {
                        $assetIds[] = $item->id;
                    }
                }
            }

            $borrowedQuantities = $this->inventoryService->getActiveBorrowedQuantitiesByItemIds($assetIds);

            return $locations->map(function (Location $location) use ($borrowedQuantities): array {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'code' => $location->code,
                    'items' => $location->items->map(function (Item $item) use ($borrowedQuantities): array {
                        $stockStatus = 'ok';
                        if ($item->current_stock <= 0) {
                            $stockStatus = 'empty';
                        } elseif ($item->current_stock <= $item->minimum_stock) {
                            $stockStatus = 'low';
                        }

                        $borrowedQty = $item->type === 'asset'
                            ? (int) ($borrowedQuantities[$item->id] ?? 0)
                            : null;

                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'type' => $item->type,
                            'unit' => $item->unit,
                            'image_url' => $item->getFirstMediaUrl('item_images') ?: null,
                            'current_stock' => $item->current_stock,
                            'minimum_stock' => $item->minimum_stock,
                            'stock_status' => $stockStatus,
                            'borrowed_quantity' => $borrowedQty,
                        ];
                    })->values()->all(),
                ];
            })->values()->all();
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
