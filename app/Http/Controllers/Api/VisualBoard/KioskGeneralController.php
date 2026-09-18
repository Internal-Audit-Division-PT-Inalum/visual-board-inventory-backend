<?php

namespace App\Http\Controllers\Api\VisualBoard;

use App\Domains\VisualBoard\Models\GeneralDocument;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\AbnormalityRepositoryInterface;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KioskGeneralController extends Controller
{
    public function __construct(
        private AbnormalityRepositoryInterface $abnormalityRepository
    ) {}

    /**
     * Ambil data Trend Abnormality berdasarkan tahun.
     *
     * Data dihitung secara OTOMATIS dari tabel abnormalities (Single Source of Truth).
     * Tidak lagi mengandalkan input manual dari tabel trend_abnormalities.
     *
     * Struktur respons: per zona, per bulan (Jan-Des), berisi X, O, Δ.
     */
    public function trendAbnormality(Request $request): JsonResponse
    {
        $year = (int) $request->input('year', now()->year);

        $matrix = $this->abnormalityRepository->getMonthlyTrendMatrix($year);

        return ApiResponse::success([
            'year' => $year,
            'matrix' => $matrix,
        ], 'Data trend abnormality berhasil dimuat.');
    }

    /**
     * Ambil daftar dokumen papan visual aktif berdasarkan kategori.
     */
    public function generalDocuments(Request $request): JsonResponse
    {
        $query = GeneralDocument::visualBoard()->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at');

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $documents = $query->get()->map(fn ($doc) => [
            'id' => $doc->id,
            'title' => $doc->title,
            'description' => $doc->description,
            'category' => $doc->category,
            'document_url' => $doc->getFirstMediaUrl('document'),
            'mime_type' => $doc->getFirstMedia('document')?->mime_type,
        ]);

        return ApiResponse::success($documents, 'Dokumen papan visual berhasil dimuat.');
    }
}
