<?php

namespace App\Filament\Resources\Items\Pages;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\Item;
use App\Filament\Resources\Items\ItemResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function afterCreate(): void
    {
        /** @var Item $record */
        $record = $this->record;

        if ($record->current_stock > 0) {
            $userId = auth()->id() ?? User::first()?->id;

            if ($userId) {
                $record->ledgers()->create([
                    'user_id' => $userId,
                    'type' => 'in',
                    'quantity' => (int) $record->current_stock,
                    'stock_before' => 0,
                    'stock_after' => (int) $record->current_stock,
                    'notes' => 'Saldo awal barang saat registrasi di sistem',
                    'reference_number' => 'INIT-' . strtoupper(Str::random(6)),
                    'occurred_at' => now(),
                ]);

                Cache::forget('inventory:kiosk:locations');

                Log::info('Inventory: initial stock ledger recorded', [
                    'item_id' => $record->id,
                    'item_name' => $record->name,
                    'quantity' => $record->current_stock,
                    'user_id' => $userId,
                ]);
            }
        }
    }
}
