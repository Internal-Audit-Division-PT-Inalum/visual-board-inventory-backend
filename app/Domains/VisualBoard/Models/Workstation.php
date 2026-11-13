<?php

namespace App\Domains\VisualBoard\Models;

use App\Domains\HR\Models\Employee;
use App\Domains\Inventory\Models\Item;
use App\Shared\Concerns\HasUlid;
use Database\Factories\WorkstationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property Collection<int, MasterWorkstationCriteria>|null $master_criterias
 */
class Workstation extends Model implements HasMedia
{
    /** @use HasFactory<WorkstationFactory> */
    use HasFactory;

    use HasUlid, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'zone_id',
        'employee_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo<Zone, $this>
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsToMany<Item, $this>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'workstation_items')
            ->withPivot('standard_quantity', 'actual_quantity')
            ->withTimestamps();
    }

    /**
     * @return HasMany<ScheduleRecord, $this>
     */
    public function scheduleRecords(): HasMany
    {
        return $this->hasMany(ScheduleRecord::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('standard_images')
            ->singleFile()
            ->useDisk('public');
    }
}
