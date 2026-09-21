<?php

namespace App\Domains\VisualBoard\Models;

use App\Domains\HR\Models\Employee;
use App\Domains\Inventory\Models\Item;
use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Workstation extends Model implements HasMedia
{
    use HasFactory, HasUlid, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'zone_id',
        'employee_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'workstation_items')
            ->withPivot('standard_quantity', 'actual_quantity')
            ->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('standard_images')
            ->useDisk('public')
            ->useFallbackUrl('/images/default-workstation.png');
    }
}
