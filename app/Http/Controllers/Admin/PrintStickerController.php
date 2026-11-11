<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use App\Http\Controllers\Controller;
use App\Services\Inventory\QrCodeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintStickerController extends Controller
{
    public function printLocations(Request $request, QrCodeService $qrCodeService): View
    {
        $rawIds = $request->input('ids');
        $ids = is_array($rawIds) ? $rawIds : array_filter(explode(',', (string) $rawIds));

        $locations = Location::whereIn('id', $ids)->get();

        if ($locations->isEmpty()) {
            abort(404, 'Data rak atau lokasi gudang tidak ditemukan untuk dicetak.');
        }

        $logoBase64 = $qrCodeService->getInalumLogoBase64();

        $stickers = $locations->map(function (Location $location) use ($qrCodeService): array {
            return [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'description' => $location->description,
                'qr_svg' => $qrCodeService->generateLocationQrSvg($location, 240),
            ];
        });

        return view('filament.print.location-qr-sticker', [
            'stickers' => $stickers,
            'logoBase64' => $logoBase64,
        ]);
    }

    public function printItems(Request $request, QrCodeService $qrCodeService): View
    {
        $rawIds = $request->input('ids');
        $ids = is_array($rawIds) ? $rawIds : array_filter(explode(',', (string) $rawIds));

        $items = Item::whereIn('id', $ids)->with('location')->get();

        if ($items->isEmpty()) {
            abort(404, 'Data barang atau aset tidak ditemukan untuk dicetak.');
        }

        $logoBase64 = $qrCodeService->getInalumLogoBase64();

        $stickers = $items->map(function (Item $item) use ($qrCodeService): array {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'type' => $item->type,
                'location_name' => $item->location->name ?? '-',
                'qr_svg' => $qrCodeService->generateItemQrSvg($item, 180),
            ];
        });

        return view('filament.print.item-qr-sticker', [
            'stickers' => $stickers,
            'logoBase64' => $logoBase64,
        ]);
    }
}
