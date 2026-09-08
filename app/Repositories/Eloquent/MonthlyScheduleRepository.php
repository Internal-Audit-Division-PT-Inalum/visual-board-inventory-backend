<?php

namespace App\Repositories\Eloquent;

use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Repositories\Contracts\MonthlyScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class MonthlyScheduleRepository extends BaseRepository implements MonthlyScheduleRepositoryInterface
{
    public function __construct(MonthlySchedule $model)
    {
        parent::__construct($model);
    }

    public function findByZoneAndPeriod(string $zoneId, string $periodMonth): ?Model
    {
        return $this->model
            ->where('zone_id', $zoneId)
            ->where('period_month', $periodMonth)
            ->first();
    }

    public function getScheduleWithRecords(string $scheduleId): ?Model
    {
        return $this->model
            ->with(['records.criteria', 'zone.picUtama', 'zone.picPengganti'])
            ->find($scheduleId);
    }
}
