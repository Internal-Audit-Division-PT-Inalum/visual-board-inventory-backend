<?php

namespace App\Services\Inventory;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Exceptions\IdempotencyConflictException;
use App\Domains\Inventory\Exceptions\InsufficientStockException;
use App\Domains\Inventory\Exceptions\InvalidItemOperationException;
use App\Domains\Inventory\Exceptions\LoanExceedsOutstandingException;
use App\Domains\Inventory\Models\AssetLoan;
use App\Domains\Inventory\Models\AssetLoanReturn;
use App\Domains\Inventory\Models\InventoryLedger;
use App\Domains\Inventory\Models\Item;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
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
    public function takeItem(string $identifier, int $quantity, string $userId, ?string $notes = null, ?string $referenceNumber = null, ?string $clientUuid = null, ?string $occurredAt = null): InventoryLedger
    {
        return $this->processTransaction('out', 'consumable', $identifier, $quantity, $userId, $notes, $referenceNumber, $clientUuid, $occurredAt);
    }

    /**
     * Tambah stok barang consumable.
     * WAJIB DB::transaction + lockForUpdate (GEMINI.md §2).
     */
    public function addItem(string $identifier, int $quantity, string $userId, ?string $notes = null, ?string $referenceNumber = null, ?string $clientUuid = null, ?string $occurredAt = null): InventoryLedger
    {
        return $this->processTransaction('in', 'consumable', $identifier, $quantity, $userId, $notes, $referenceNumber, $clientUuid, $occurredAt);
    }

    /**
     * Pinjam barang asset.
     * WAJIB DB::transaction + lockForUpdate (GEMINI.md §2).
     */
    public function borrowItem(string $identifier, int $quantity, string $userId, ?string $notes = null, ?string $referenceNumber = null, ?string $clientUuid = null, ?string $occurredAt = null): InventoryLedger
    {
        return $this->processTransaction('borrow', 'asset', $identifier, $quantity, $userId, $notes, $referenceNumber, $clientUuid, $occurredAt);
    }

    /**
     * Kembalikan barang asset.
     * WAJIB DB::transaction + lockForUpdate (GEMINI.md §2).
     */
    public function returnItem(string $identifier, int $quantity, string $userId, ?string $notes = null, ?string $referenceNumber = null, ?string $clientUuid = null, ?string $occurredAt = null): InventoryLedger
    {
        return $this->processTransaction('return', 'asset', $identifier, $quantity, $userId, $notes, $referenceNumber, $clientUuid, $occurredAt);
    }

    /**
     * Helper to process transaction logic cleanly with idempotency check
     */
    private function processTransaction(string $type, string $expectedItemType, string $identifier, int $quantity, string $userId, ?string $notes, ?string $referenceNumber, ?string $clientUuid, ?string $occurredAt): InventoryLedger
    {
        Log::info('processTransaction', ['clientUuid' => $clientUuid]);

        return DB::transaction(function () use ($type, $expectedItemType, $identifier, $quantity, $userId, $notes, $referenceNumber, $clientUuid, $occurredAt) {
            // 1. Idempotency Check early
            if ($clientUuid) {
                $existing = InventoryLedger::where('client_uuid', $clientUuid)->first();
                if ($existing) {
                    if ($existing->user_id === $userId) {
                        $existing->setAttribute('is_replayed', true);

                        return $existing;
                    }

                    throw new IdempotencyConflictException;
                }
            }

            // 2. Lock item
            $item = Item::where(function ($q) use ($identifier) {
                $q->where('id', $identifier)->orWhere('sku', $identifier);
            })->lockForUpdate()->first();

            if (! $item) {
                throw new NotFoundHttpException('Item tidak ditemukan.');
            }

            // 3. Verify item type
            if ($item->type !== $expectedItemType) {
                throw new InvalidItemOperationException(
                    "Item \"{$item->name}\" bertipe {$item->type}. Operasi {$type} hanya untuk {$expectedItemType}."
                );
            }

            // 3.5. Validation for Loans
            $user = null;
            if (in_array($type, ['borrow', 'return'])) {
                $user = User::with('employee')->find($userId);
                if (! $user || ! $user->employee) {
                    throw new InvalidItemOperationException('Hanya pegawai yang dapat melakukan peminjaman/pengembalian.');
                }
            }

            $openLoans = null;
            if ($type === 'return') {
                $openLoans = AssetLoan::where('item_id', $item->id)
                    ->where('employee_id', $user->employee->id)
                    ->whereNull('fully_returned_at')
                    ->lockForUpdate()
                    ->orderBy('borrowed_at', 'asc')
                    ->get();

                $totalOutstanding = $openLoans->sum(fn ($loan) => $loan->quantity - $loan->returned_quantity);
                if ($quantity > $totalOutstanding) {
                    throw new LoanExceedsOutstandingException(
                        "Jumlah pengembalian ({$quantity}) melebihi sisa pinjaman ({$totalOutstanding})."
                    );
                }
            }

            $stockBefore = $item->current_stock;
            $stockAfter = $stockBefore;

            // 4. Calculate stock based on type
            if (in_array($type, ['out', 'borrow'])) {
                if ($item->current_stock < $quantity) {
                    throw new InsufficientStockException(
                        "Stok {$item->name} tidak mencukupi (tersedia: {$item->current_stock}, diminta: {$quantity})."
                    );
                }
                $stockAfter = $stockBefore - $quantity;
            } elseif (in_array($type, ['in', 'return'])) {
                $stockAfter = $stockBefore + $quantity;
            }

            $item->update(['current_stock' => $stockAfter]);

            // 5. Create Ledger with conflict handling
            try {
                $ledger = InventoryLedger::create([
                    'item_id' => $item->id,
                    'user_id' => $userId,
                    'type' => $type,
                    'quantity' => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'notes' => $notes,
                    'reference_number' => $referenceNumber,
                    'client_uuid' => $clientUuid,
                    'occurred_at' => $occurredAt,
                ]);
            } catch (QueryException $e) {
                if ($clientUuid && $e->getCode() == '23505' && str_contains($e->getMessage(), 'inventory_ledgers_client_uuid_unique')) {
                    $existing = InventoryLedger::where('client_uuid', $clientUuid)->first();
                    if ($existing && $existing->user_id === $userId) {
                        $existing->setAttribute('is_replayed', true);

                        return $existing;
                    }

                    throw new IdempotencyConflictException;
                }

                throw $e;
            }

            // 5.5. Manage Asset Loans
            if ($type === 'borrow') {
                AssetLoan::create([
                    'item_id' => $item->id,
                    'employee_id' => $user->employee->id,
                    'quantity' => $quantity,
                    'borrowed_at' => $occurredAt ? Carbon::parse($occurredAt) : now(),
                    'borrow_ledger_id' => $ledger->id,
                ]);
            } elseif ($type === 'return') {
                $remainingToReturn = $quantity;
                foreach ($openLoans as $loan) {
                    if ($remainingToReturn <= 0) {
                        break;
                    }

                    $loanOutstanding = $loan->quantity - $loan->returned_quantity;
                    $returningForThisLoan = min($remainingToReturn, $loanOutstanding);

                    $loan->returned_quantity += $returningForThisLoan;
                    if ($loan->returned_quantity >= $loan->quantity) {
                        $loan->fully_returned_at = $occurredAt ? Carbon::parse($occurredAt) : now();
                    }
                    $loan->save();

                    AssetLoanReturn::create([
                        'asset_loan_id' => $loan->id,
                        'ledger_id' => $ledger->id,
                        'quantity' => $returningForThisLoan,
                        'returned_at' => $occurredAt ? Carbon::parse($occurredAt) : now(),
                    ]);

                    $remainingToReturn -= $returningForThisLoan;
                }
            }

            // 6. Logging
            $actionWord = match ($type) {
                'out' => 'taken',
                'in' => 'restocked',
                'borrow' => 'borrowed',
                'return' => 'returned',
                default => 'processed'
            };

            Log::info("Inventory: {$expectedItemType} {$actionWord}", [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'user_id' => $userId,
                'quantity' => $quantity,
                'stock_after' => $stockAfter,
                'client_uuid' => $clientUuid,
            ]);

            if (in_array($type, ['out', 'borrow']) && $stockAfter <= $item->minimum_stock) {
                Log::warning("Inventory: low {$expectedItemType} stock warning", [
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
     * Riwayat transaksi per item (paginated, terbaru di atas).
     */
    public function getLedgerHistory(string $identifier, int $perPage = 15): LengthAwarePaginator
    {
        $item = Item::where('id', $identifier)->orWhere('sku', $identifier)->firstOrFail();

        return InventoryLedger::where('item_id', $item->id)
            ->with(['user'])
            ->latest()
            ->paginate($perPage);
    }
}
