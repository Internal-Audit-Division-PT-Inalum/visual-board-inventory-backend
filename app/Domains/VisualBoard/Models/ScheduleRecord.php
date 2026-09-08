<?php

namespace App\Domains\VisualBoard\Models;

use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleRecord extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'monthly_schedule_id',
        'inspection_criteria_id',
        'days_data',
    ];

    protected $casts = [
        'days_data' => 'array',
    ];

    public function monthlySchedule(): BelongsTo
    {
        return $this->belongsTo(MonthlySchedule::class);
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(InspectionCriteria::class, 'inspection_criteria_id');
    }
}
