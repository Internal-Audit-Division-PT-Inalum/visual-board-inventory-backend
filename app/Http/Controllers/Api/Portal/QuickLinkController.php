<?php

namespace App\Http\Controllers\Api\Portal;

use App\Domains\Portal\Models\QuickLink;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class QuickLinkController extends Controller
{
    public function kioskIndex(): JsonResponse
    {
        $links = QuickLink::where('is_active', true)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Active quick links retrieved successfully',
            'data' => $links,
        ]);
    }
}
