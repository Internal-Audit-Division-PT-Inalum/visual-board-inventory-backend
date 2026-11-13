<?php

namespace App\Repositories\Eloquent;

use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Repositories\Contracts\MonthlyScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<MonthlySchedule>
 */
class MonthlyScheduleRepository extends BaseRepository implements MonthlyScheduleRepositoryInterface
{
    public function __construct(MonthlySchedule $model)
    {
        parent::__construct($model);
    }

    /**
     * @return Builder<MonthlySchedule>
     */
    protected function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * @return Collection<int, MonthlySchedule>
     */
    public function all(): Collection
    {
        return $this->newQuery()->get();
    }

    public function findById(string $id): ?MonthlySchedule
    {
        return $this->model->newQuery()->find($id);
    }

    public function findByZoneAndPeriod(string $zoneId, string $periodMonth): ?MonthlySchedule
    {
        return $this->model->newQuery()
            ->where('zone_id', $zoneId)
            ->where('period_month', $periodMonth)
            ->first();
    }

    /**
     * @return Collection<int, MonthlySchedule>
     */
    public function getAllByPeriod(string $periodMonth): Collection
    {
        return $this->model->newQuery()
            ->with(['scheduleRecords.criteria', 'scheduleRecords.workstation', 'scheduleRecords.masterWorkstationCriteria', 'zone.picUtama', 'zone.picPengganti'])
            ->where('period_month', $periodMonth)
            ->get();
    }

    public function getScheduleWithRecords(string $scheduleId): ?MonthlySchedule
    {
        return $this->model->newQuery()
            ->with(['scheduleRecords.criteria', 'scheduleRecords.workstation', 'scheduleRecords.masterWorkstationCriteria', 'zone.picUtama', 'zone.picPengganti'])
            ->find($scheduleId);
    }
}
