<?php

namespace App\Services\Inventory;

use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    /**
     * Generate pure SVG string for a given payload.
     */
    public function generateSvg(string $content, int $size = 200, int $margin = 1): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, $margin),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);

        return $writer->writeString($content);
    }

    /**
     * Generate QR Code SVG for a warehouse Location (Rack).
     * Format: https://inventory.inalum.id/?src=qr&location_id={location->id}
     */
    public function generateLocationQrSvg(Location $location, int $size = 200): string
    {
        /** @var string $baseUrl */
        $baseUrl = config('app.inventory_frontend_url', 'https://inventory.inalum.id');
        $url = rtrim($baseUrl, '/') . '/?src=qr&location_id=' . urlencode($location->id);

        return $this->generateSvg($url, $size, 1);
    }

    /**
     * Generate QR Code SVG for an inventory Item.
     * Format: https://inventory.inalum.id/?src=qr&item={item->sku}
     */
    public function generateItemQrSvg(Item $item, int $size = 200): string
    {
        /** @var string $baseUrl */
        $baseUrl = config('app.inventory_frontend_url', 'https://inventory.inalum.id');
        $url = rtrim($baseUrl, '/') . '/?src=qr&item=' . urlencode($item->sku);

        return $this->generateSvg($url, $size, 1);
    }

    /**
     * Get Base64 Data URI of PT INALUM official logo for embedding in thermal stickers.
     */
    public function getInalumLogoBase64(): ?string
    {
        $path = public_path('images/inalum-logo.png');

        if (! file_exists($path)) {
            return null;
        }

        $data = file_get_contents($path);
        if ($data === false) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($data);
    }
}
