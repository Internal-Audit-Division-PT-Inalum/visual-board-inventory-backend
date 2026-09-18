<?php

namespace App\Http\Controllers\Api\HR;

use App\Domains\VisualBoard\Models\GeneralDocument;
use App\Http\Controllers\Controller;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KioskOrganizationController extends Controller
{
    /**
     * Mengambil daftar dokumen organisasi aktif (Bagan Struktur / Map Area 5R).
     *
     * Data bersumber dari tabel `general_documents` dengan domain='organization',
     * hasil unifikasi dari OrganizationDocument (deprecated).
     */
    public function documents(Request $request): JsonResponse
    {
        $documents = GeneralDocument::organization()
            ->where('is_active', true)
            ->when(
                $request->filled('category'),
                fn ($query) => $query->where('category', $request->input('category'))
            )
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'title' => $doc->title,
                'description' => $doc->description,
                'category' => $doc->category,
                // Nama field tetap 'image_url' agar tidak breaking change di Frontend
                'image_url' => $doc->getFirstMediaUrl('document') ?: null,
                'mime_type' => $doc->getFirstMedia('document')?->mime_type,
            ]);

        return ApiResponse::success(
            $documents->toArray(),
            'Dokumen organisasi berhasil dimuat.'
        );
    }
}
