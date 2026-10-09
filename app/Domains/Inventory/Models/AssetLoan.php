<?php

namespace App\Domains\Inventory\Models;

use App\Domains\HR\Models\Employee;
use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetLoan extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'item_id',
        'employee_id',
        'quantity',
        'returned_quantity',
        'borrowed_at',
        'fully_returned_at',
        'borrow_ledger_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'returned_quantity' => 'integer',
        'borrowed_at' => 'datetime',
        'fully_returned_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function borrowLedger(): BelongsTo
    {
        return $this->belongsTo(InventoryLedger::class, 'borrow_ledger_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(AssetLoanReturn::class);
    }
}
