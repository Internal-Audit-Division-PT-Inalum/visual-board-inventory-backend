<?php

namespace App\Domains\VisualBoard\Models;

use App\Models\User;
use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlySchedule extends Model
{
    use HasUlid, SoftDeletes;

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

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(ScheduleRecord::class);
    }
}
