<?php

namespace App\Domains\VisualBoard\Models;

use App\Domains\Core\Models\User;
use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Abnormality extends Model implements HasMedia
{
    use HasFactory, HasUlid, SoftDeletes, InteractsWithMedia;

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
        'reported_by_id',
        'status',
        'progress_percentage',
        'is_kaizen',
        'verified_by_staff_id',
        'verified_at_staff',
        'verified_by_ms_id',
        'verified_at_ms',
    ];

    protected $casts = [
        'date_found' => 'date',
        'target_date' => 'date',
        'actual_resolution_date' => 'date',
        'progress_percentage' => 'integer',
        'is_kaizen' => 'boolean',
        'verified_at_staff' => 'datetime',
        'verified_at_ms' => 'datetime',
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

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function verifiedByStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_staff_id');
    }

    public function verifiedByMs(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_ms_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('kaizen_reports')
            ->singleFile() // Hanya 1 file per abnormality
            ->acceptsMimeTypes([
                'application/pdf',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        $this->addMediaCollection('evidence_photos')
            ->useFallbackUrl('/images/no-evidence.png');

        $this->addMediaCollection('resolution_photos')
            ->useFallbackUrl('/images/no-resolution.png');
    }
}
