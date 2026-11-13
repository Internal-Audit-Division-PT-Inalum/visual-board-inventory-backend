<?php

namespace App\Domains\Inventory\Models;

use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLoanReturn extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    use HasUlid;

    protected $fillable = [
        'asset_loan_id',
        'ledger_id',
        'quantity',
        'returned_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'returned_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<AssetLoan, $this>
     */
    public function assetLoan(): BelongsTo
    {
        return $this->belongsTo(AssetLoan::class);
    }

    /**
     * @return BelongsTo<InventoryLedger, $this>
     */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(InventoryLedger::class);
    }
}
