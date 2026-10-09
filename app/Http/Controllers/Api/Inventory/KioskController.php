<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Domains\Inventory\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KioskController extends Controller
{
    public function locations(Request $request)
    {
        $data = Cache::remember('inventory:kiosk:locations', now()->addMinutes(5), function () {
            $locations = Location::with(['items' => function ($query) {
                $query->where('is_active', true);
            }])->get();

            $assetIds = [];
            foreach ($locations as $location) {
                foreach ($location->items as $item) {
                    if ($item->type === 'asset') {
                        $assetIds[] = $item->id;
                    }
                }
            }

            $borrowedQuantities = [];
            if (! empty($assetIds)) {
                $borrowedQuantities = DB::table('asset_loans')
                    ->whereIn('item_id', $assetIds)
                    ->whereNull('fully_returned_at')
                    ->select('item_id', DB::raw('SUM(quantity - returned_quantity) as total_borrowed'))
                    ->groupBy('item_id')
                    ->pluck('total_borrowed', 'item_id')
                    ->toArray();
            }

            return $locations->map(function ($location) use ($borrowedQuantities) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'code' => $location->code,
                    'items' => $location->items->map(function ($item) use ($borrowedQuantities) {
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
                    })->values()->toArray(),
                ];
            })->values()->toArray();
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
