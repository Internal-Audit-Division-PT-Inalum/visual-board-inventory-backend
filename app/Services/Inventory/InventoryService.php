<?php

namespace App\Services\Inventory;

use App\Domains\Inventory\Exceptions\InsufficientStockException;
use App\Domains\Inventory\Exceptions\InvalidItemOperationException;
use App\Domains\Inventory\Models\InventoryLedger;
use App\Domains\Inventory\Models\Item;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InventoryService
{
    /**
     * Ambil barang consumable dari stok.
     * WAJIB DB::transaction + lockForUpdate (GEMINI.md §2).
     */
    public function takeItem(string $itemId, int $quantity, string $userId, ?string $notes = null, ?string $referenceNumber = null): InventoryLedger
    {
        return DB::transaction(function () use ($itemId, $quantity, $userId, $notes, $referenceNumber) {
            $item = Item::where('id', $itemId)->lockForUpdate()->first();

            if (! $item) {
                throw new NotFoundHttpException('Item tidak ditemukan.');
            }

            // Hanya consumable yang bisa di-take (GEMINI.md §6)
            if ($item->type !== 'consumable') {
                throw new InvalidItemOperationException(
                    "Item \"{$item->name}\" bertipe asset. Gunakan endpoint borrow untuk meminjam asset."
                );
            }

            if ($item->current_stock < $quantity) {
                throw new InsufficientStockException(
                    "Stok {$item->name} tidak mencukupi (sisa: {$item->current_stock}, diminta: {$quantity})."
                );
            }

            $stockBefore = $item->current_stock;
            $stockAfter = $stockBefore - $quantity;

            $item->update(['current_stock' => $stockAfter]);

            $ledger = InventoryLedger::create([
                'item_id' => $item->id,
                'user_id' => $userId,
                'type' => 'out',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $notes,
                'reference_number' => $referenceNumber,
            ]);

            // Structured logging (GEMINI.md §8)
            Log::info('Inventory: item taken (consumable)', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'user_id' => $userId,
                'quantity' => $quantity,
                'stock_after' => $stockAfter,
            ]);

            // Peringatan stok rendah (GEMINI.md §8)
            if ($stockAfter <= $item->minimum_stock) {
                Log::warning('Inventory: low stock warning', [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'current_stock' => $stockAfter,
                    'minimum_stock' => $item->minimum_stock,
                ]);
            }

            return $ledger;
        });
    }

    /**
     * Tambah stok barang consumable.
     * WAJIB DB::transaction + lockForUpdate (GEMINI.md §2).
     */
    public function addItem(string $itemId, int $quantity, string $userId, ?string $notes = null, ?string $referenceNumber = null): InventoryLedger
    {
        return DB::transaction(function () use ($itemId, $quantity, $userId, $notes, $referenceNumber) {
            $item = Item::where('id', $itemId)->lockForUpdate()->first();

            if (! $item) {
                throw new NotFoundHttpException('Item tidak ditemukan.');
            }

            // Hanya consumable yang bisa di-add (restock)
            if ($item->type !== 'consumable') {
                throw new InvalidItemOperationException(
                    "Item \"{$item->name}\" bertipe asset. Gunakan endpoint return untuk mengembalikan asset."
                );
            }

            $stockBefore = $item->current_stock;
            $stockAfter = $stockBefore + $quantity;

            $item->update(['current_stock' => $stockAfter]);

            $ledger = InventoryLedger::create([
                'item_id' => $item->id,
                'user_id' => $userId,
                'type' => 'in',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $notes,
                'reference_number' => $referenceNumber,
            ]);

            Log::info('Inventory: item restocked (consumable)', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'user_id' => $userId,
                'quantity' => $quantity,
                'stock_after' => $stockAfter,
            ]);

            return $ledger;
        });
    }

    /**
     * Pinjam barang asset.
     * WAJIB DB::transaction + lockForUpdate (GEMINI.md §2).
     */
    public function borrowItem(string $itemId, int $quantity, string $userId, ?string $notes = null, ?string $referenceNumber = null): InventoryLedger
    {
        return DB::transaction(function () use ($itemId, $quantity, $userId, $notes, $referenceNumber) {
            $item = Item::where('id', $itemId)->lockForUpdate()->first();

            if (! $item) {
                throw new NotFoundHttpException('Item tidak ditemukan.');
            }

            if ($item->type !== 'asset') {
                throw new InvalidItemOperationException(
                    "Item \"{$item->name}\" bertipe consumable. Gunakan endpoint take untuk mengambil consumable."
                );
            }

            if ($item->current_stock < $quantity) {
                throw new InsufficientStockException(
                    "Stok {$item->name} tidak mencukupi untuk dipinjam (tersedia: {$item->current_stock}, diminta: {$quantity})."
                );
            }

            $stockBefore = $item->current_stock;
            $stockAfter = $stockBefore - $quantity;

            $item->update(['current_stock' => $stockAfter]);

            $ledger = InventoryLedger::create([
                'item_id' => $item->id,
                'user_id' => $userId,
                'type' => 'borrow',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $notes,
                'reference_number' => $referenceNumber,
            ]);

            Log::info('Inventory: asset borrowed', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'user_id' => $userId,
                'quantity' => $quantity,
                'stock_after' => $stockAfter,
            ]);

            if ($stockAfter <= $item->minimum_stock) {
                Log::warning('Inventory: low asset stock warning', [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'current_stock' => $stockAfter,
                    'minimum_stock' => $item->minimum_stock,
                ]);
            }

            return $ledger;
        });
    }

    /**
     * Kembalikan barang asset.
     * WAJIB DB::transaction + lockForUpdate (GEMINI.md §2).
     */
    public function returnItem(string $itemId, int $quantity, string $userId, ?string $notes = null, ?string $referenceNumber = null): InventoryLedger
    {
        return DB::transaction(function () use ($itemId, $quantity, $userId, $notes, $referenceNumber) {
            $item = Item::where('id', $itemId)->lockForUpdate()->first();

            if (! $item) {
                throw new NotFoundHttpException('Item tidak ditemukan.');
            }

            if ($item->type !== 'asset') {
                throw new InvalidItemOperationException(
                    "Item \"{$item->name}\" bertipe consumable. Operasi return hanya untuk asset."
                );
            }

            $stockBefore = $item->current_stock;
            $stockAfter = $stockBefore + $quantity;

            $item->update(['current_stock' => $stockAfter]);

            $ledger = InventoryLedger::create([
                'item_id' => $item->id,
                'user_id' => $userId,
                'type' => 'return',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $notes,
                'reference_number' => $referenceNumber,
            ]);

            Log::info('Inventory: asset returned', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'user_id' => $userId,
                'quantity' => $quantity,
                'stock_after' => $stockAfter,
            ]);

            return $ledger;
        });
    }

    /**
     * Riwayat transaksi per item (paginated, terbaru di atas).
     */
    public function getLedgerHistory(string $itemId, int $perPage = 15): LengthAwarePaginator
    {
        return InventoryLedger::where('item_id', $itemId)
            ->with(['user'])
            ->latest()
            ->paginate($perPage);
    }
}
