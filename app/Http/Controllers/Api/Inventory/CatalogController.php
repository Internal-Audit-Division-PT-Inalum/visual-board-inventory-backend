<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Domains\Inventory\Models\Item;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::with('location')
            ->where('is_active', true)
            ->get();

        $generatedAt = now()->toIso8601String();
        $etag = md5($items->max('updated_at') . $items->count());
        $clientEtag = str_replace('"', '', $request->header('If-None-Match', ''));

        if ($clientEtag === $etag) {
            return response()->json(null, 304);
        }

        // We need to calculate borrowed_quantity for each asset
        // borrowed_quantity = sum of open asset_loans (quantity - returned_quantity)
        $itemIds = $items->where('type', 'asset')->pluck('id');

        $borrowedQuantities = [];
        if ($itemIds->isNotEmpty()) {
            $borrowedQuantities = DB::table('asset_loans')
                ->whereIn('item_id', $itemIds)
                ->whereNull('fully_returned_at')
                ->select('item_id', DB::raw('SUM(quantity - returned_quantity) as total_borrowed'))
                ->groupBy('item_id')
                ->pluck('total_borrowed', 'item_id')
                ->toArray();
        }

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
