<?php

namespace App\Domains\VisualBoard\Models;

use App\Domains\Core\Models\User;
use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Abnormality extends Model
{
    use HasFactory, HasUlid, SoftDeletes;

    protected $fillable = [
        'zone_id',
        'monthly_schedule_id',
        'inspection_criteria_id',
        'date_found',
        'problem_description',
        'countermeasure_plan',
        'countermeasure_actual',
        'target_date',
        'actual_resolution_date',
        'pic_id',
        'status',
        'progress_percentage',
        'is_kaizen',
    ];

    protected $casts = [
        'date_found' => 'date',
        'target_date' => 'date',
        'actual_resolution_date' => 'date',
        'progress_percentage' => 'integer',
        'is_kaizen' => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function monthlySchedule(): BelongsTo
    {
        return $this->belongsTo(MonthlySchedule::class);
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(InspectionCriteria::class, 'inspection_criteria_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }
}
