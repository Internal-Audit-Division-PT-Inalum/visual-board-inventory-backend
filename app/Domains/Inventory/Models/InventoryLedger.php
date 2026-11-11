<?php

namespace App\Domains\Inventory\Models;

use App\Domains\Core\Models\User;
use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $item_id
 * @property string $user_id
 * @property string $type
 * @property int $quantity
 * @property int $stock_before
 * @property int $stock_after
 * @property string|null $notes
 * @property string|null $reference_number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Item|null $item
 * @property-read User|null $user
 */
class InventoryLedger extends Model
{
    use HasFactory, HasUlid;

    // Append-only — TIDAK pakai SoftDeletes (AGENTS.md konvensi)

    protected $fillable = [
        'client_uuid',
        'occurred_at',
        'item_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'notes',
        'reference_number',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    /**
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
