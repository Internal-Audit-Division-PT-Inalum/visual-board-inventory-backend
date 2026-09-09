<?php

namespace App\Domains\Inventory\Models;

use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, HasUlid, SoftDeletes;

    protected $fillable = [
        'location_id',
        'name',
        'sku',
        'type',
        'unit',
        'current_stock',
        'minimum_stock',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'current_stock' => 'integer',
        'minimum_stock' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(InventoryLedger::class);
    }
}
