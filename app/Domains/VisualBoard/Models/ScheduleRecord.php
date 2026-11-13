<?php

namespace App\Domains\VisualBoard\Models;

use App\Shared\Concerns\HasUlid;
use Database\Factories\ScheduleRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleRecord extends Model
{
    /** @use HasFactory<ScheduleRecordFactory> */
    use HasFactory;

    use HasUlid;

    protected $fillable = [
        'monthly_schedule_id',
        'inspection_criteria_id',
        'workstation_id',
        'master_workstation_criteria_id',
        'days_data',
    ];

    protected $casts = [
        'days_data' => 'array',
    ];

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
     * @return BelongsTo<Workstation, $this>
     */
    public function workstation(): BelongsTo
    {
        return $this->belongsTo(Workstation::class);
    }

    /**
     * @return BelongsTo<MasterWorkstationCriteria, $this>
     */
    public function masterWorkstationCriteria(): BelongsTo
    {
        return $this->belongsTo(MasterWorkstationCriteria::class, 'master_workstation_criteria_id');
    }
}
