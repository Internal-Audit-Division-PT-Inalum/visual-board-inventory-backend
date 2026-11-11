<?php

namespace App\Domains\VisualBoard\Models;

use App\Domains\Core\Models\User;
use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $zone_id
 * @property string $status
 * @property array<string, mixed>|null $approval_data
 * @property string|null $created_by
 * @property-read Zone|null $zone
 * @property-read User|null $creator
 */
class MonthlySchedule extends Model
{
    use HasFactory, HasUlid, SoftDeletes;

    protected $fillable = [
        'zone_id',
        'period_month',
        'status',
        'approval_data',
        'created_by',
    ];

    protected $casts = [
        'period_month' => 'date',
        'approval_data' => 'array',
    ];

    /**
     * @return BelongsTo<Zone, $this>
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<ScheduleRecord, $this>
     */
    public function scheduleRecords(): HasMany
    {
        return $this->hasMany(ScheduleRecord::class);
    }

    /**
     * @return HasMany<SchedulePic, $this>
     */
    public function schedulePics(): HasMany
    {
        return $this->hasMany(SchedulePic::class);
    }
}
