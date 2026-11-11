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

/**
 * @property string $id
 * @property string $zone_id
 * @property string|null $monthly_schedule_id
 * @property string|null $inspection_criteria_id
 * @property string $problem_description
 * @property string|null $countermeasure_actual
 * @property string|null $pic_id
 * @property string|null $reported_by_id
 * @property string $status
 * @property int $progress_percentage
 * @property string|null $verified_by_staff_id
 * @property string|null $verified_by_ms_id
 * @property-read Zone|null $zone
 * @property-read MonthlySchedule|null $monthlySchedule
 * @property-read InspectionCriteria|null $criteria
 * @property-read User|null $pic
 * @property-read User|null $reportedBy
 * @property-read User|null $verifiedByStaff
 * @property-read User|null $verifiedByMs
 */
class Abnormality extends Model implements HasMedia
{
    use HasFactory, HasUlid, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'zone_id',
        'monthly_schedule_id',
        'inspection_criteria_id',
        'date_found',
        'finder_name',
        'group_name',
        'problem_description',
        'countermeasure_plan',
        'countermeasure_actual',
        'planned_date',
        'actual_date',
        'target_date',
        'actual_resolution_date',
        'pic_id',
        'reported_by_id',
        'status',
        'progress_percentage',
        'is_kaizen',
        'signed_by_staff',
        'signed_by_ms',
        'verified_by_staff_id',
        'verified_at_staff',
        'verified_by_ms_id',
        'verified_at_ms',
    ];

    protected $casts = [
        'date_found' => 'date',
        'planned_date' => 'date',
        'actual_date' => 'date',
        'target_date' => 'date',
        'actual_resolution_date' => 'date',
        'progress_percentage' => 'integer',
        'is_kaizen' => 'boolean',
        'verified_at_staff' => 'datetime',
        'verified_at_ms' => 'datetime',
    ];

    /**
     * @return BelongsTo<Zone, $this>
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * @return BelongsTo<MonthlySchedule, $this>
     */
    public function monthlySchedule(): BelongsTo
    {
        return $this->belongsTo(MonthlySchedule::class);
    }

    /**
     * @return BelongsTo<InspectionCriteria, $this>
     */
    public function criteria(): BelongsTo
    {
        return $this->belongsTo(InspectionCriteria::class, 'inspection_criteria_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifiedByStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_staff_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
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
