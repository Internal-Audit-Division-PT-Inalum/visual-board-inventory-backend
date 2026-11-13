<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Domains\Inventory\Models\Item;
use App\Http\Controllers\Controller;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = Item::with(['location', 'media'])
            ->where('is_active', true)
            ->get();

        $generatedAt = now()->toIso8601String();
        $etag = md5($items->max('updated_at') . $items->count());
        $clientEtag = str_replace('"', '', $request->header('If-None-Match', ''));

        if ($clientEtag === $etag) {
            return response()->json(null, 304);
        }

        $assetIds = $items->where('type', 'asset')
            ->map(fn (Item $item): string => $item->id)
            ->values()
            ->all();

        $borrowedQuantities = $this->inventoryService->getActiveBorrowedQuantitiesByItemIds($assetIds);

        $data = $items->map(function ($item) use ($borrowedQuantities) {
            $borrowedQty = $item->type === 'asset'
                ? (int) ($borrowedQuantities[$item->id] ?? 0)
                : null;

            return [
                'id' => $item->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'type' => $item->type,
                'unit' => $item->unit,
                'location_id' => $item->location_id,
                'current_stock' => $item->current_stock,
                'minimum_stock' => $item->minimum_stock,
                'image_thumb_url' => $item->getFirstMediaUrl('item_images', 'thumb') ?: null,
                'updated_at' => $item->updated_at,
                'borrowed_quantity' => $borrowedQty,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'generated_at' => $generatedAt,
            ],
        ])->setEtag($etag);
    }
}
