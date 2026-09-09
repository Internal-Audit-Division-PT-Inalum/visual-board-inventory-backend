<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreItemRequest;
use App\Http\Requests\Inventory\TransactItemRequest;
use App\Http\Resources\Api\Inventory\ItemResource;
use App\Http\Resources\Api\Inventory\LedgerResource;
use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Services\Inventory\InventoryService;
use App\Shared\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Inventory Domain
 *
 * @subgroup Manajemen Barang & Transaksi
 *
 * Endpoint CRUD untuk master data barang, serta operasi transaksional
 * (take/add untuk consumable, borrow/return untuk asset).
 */
class ItemController extends Controller
{
    public function __construct(
        private ItemRepositoryInterface $repository,
        private InventoryService $service
    ) {}

    /**
     * Daftar Barang (Paginated)
     *
     * Filter opsional: `location_id`, `type` (consumable|asset), `is_active`, `search`.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['location_id', 'type', 'is_active', 'search']);
        $perPage = $request->input('per_page', 15);

        $items = $this->repository->getAllPaginated($filters, $perPage);

        return ItemResource::collection($items);
    }

    /**
     * Detail Barang
     *
     * @urlParam item string required ULID dari barang.
     */
    public function show(string $item): JsonResponse
    {
        $data = $this->repository->findById($item);

        if (! $data) {
            return ApiResponse::error('Barang tidak ditemukan.', [], 404);
        }

        return ApiResponse::success(new ItemResource($data));
    }

    /**
     * Buat Barang Baru
     */
    public function store(StoreItemRequest $request): JsonResponse
    {
        $item = $this->repository->create($request->validated());

        return ApiResponse::success(
            new ItemResource($item->load('location')),
            'Barang berhasil ditambahkan.',
            [],
            201
        );
    }

    /**
     * Hapus Barang
     *
     * @urlParam item string required ULID dari barang.
     */
    public function destroy(string $item): JsonResponse
    {
        $data = $this->repository->findById($item);

        if (! $data) {
            return ApiResponse::error('Barang tidak ditemukan.', [], 404);
        }

        $this->repository->delete($item);

        return ApiResponse::success(null, 'Barang berhasil dihapus.');
    }

    /**
     * Ambil Barang Consumable (Take)
     *
     * Mengurangi stok consumable. Dicatat di ledger sebagai tipe `out`.
     * Akan gagal jika stok tidak mencukupi atau tipe barang bukan consumable.
     *
     * @urlParam item string required ULID dari barang.
     */
    public function take(TransactItemRequest $request, string $item): JsonResponse
    {
        $ledger = $this->service->takeItem(
            $item,
            $request->validated('quantity'),
            $request->user()->id,
            $request->validated('notes'),
            $request->validated('reference_number')
        );

        return ApiResponse::success(
            new LedgerResource($ledger->load('user')),
            'Barang berhasil diambil.',
            [],
            201
        );
    }

    /**
     * Tambah Stok Consumable (Add / Restock)
     *
     * Menambah stok consumable. Dicatat di ledger sebagai tipe `in`.
     *
     * @urlParam item string required ULID dari barang.
     */
    public function add(TransactItemRequest $request, string $item): JsonResponse
    {
        $ledger = $this->service->addItem(
            $item,
            $request->validated('quantity'),
            $request->user()->id,
            $request->validated('notes'),
            $request->validated('reference_number')
        );

        return ApiResponse::success(
            new LedgerResource($ledger->load('user')),
            'Stok barang berhasil ditambahkan.',
            [],
            201
        );
    }

    /**
     * Pinjam Asset (Borrow)
     *
     * Mengurangi stok asset (dipinjam). Dicatat di ledger sebagai tipe `borrow`.
     *
     * @urlParam item string required ULID dari barang.
     */
    public function borrow(TransactItemRequest $request, string $item): JsonResponse
    {
        $ledger = $this->service->borrowItem(
            $item,
            $request->validated('quantity'),
            $request->user()->id,
            $request->validated('notes'),
            $request->validated('reference_number')
        );

        return ApiResponse::success(
            new LedgerResource($ledger->load('user')),
            'Asset berhasil dipinjam.',
            [],
            201
        );
    }

    /**
     * Kembalikan Asset (Return)
     *
     * Mengembalikan stok asset. Dicatat di ledger sebagai tipe `return`.
     *
     * @urlParam item string required ULID dari barang.
     */
    public function returnItem(TransactItemRequest $request, string $item): JsonResponse
    {
        $ledger = $this->service->returnItem(
            $item,
            $request->validated('quantity'),
            $request->user()->id,
            $request->validated('notes'),
            $request->validated('reference_number')
        );

        return ApiResponse::success(
            new LedgerResource($ledger->load('user')),
            'Asset berhasil dikembalikan.',
            [],
            201
        );
    }

    /**
     * Riwayat Transaksi Barang (Ledger)
     *
     * Mengambil audit trail seluruh transaksi pada barang tertentu.
     *
     * @urlParam item string required ULID dari barang.
     */
    public function ledger(Request $request, string $item): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $history = $this->service->getLedgerHistory($item, $perPage);

        return ApiResponse::success(
            LedgerResource::collection($history),
            'Berhasil memuat riwayat transaksi.'
        );
    }

    /**
     * Daftar Barang Stok Rendah
     *
     * Mengambil item yang current_stock <= minimum_stock.
     */
    public function lowStock(): JsonResponse
    {
        $items = $this->repository->getLowStockItems();

        return ApiResponse::success(
            ItemResource::collection($items),
            'Berhasil memuat daftar barang stok rendah.'
        );
    }
}
