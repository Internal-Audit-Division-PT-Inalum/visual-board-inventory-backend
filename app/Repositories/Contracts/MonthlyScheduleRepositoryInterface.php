<?php

namespace App\Repositories\Contracts;

use App\Domains\VisualBoard\Models\MonthlySchedule;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends RepositoryInterface<MonthlySchedule>
 */
interface MonthlyScheduleRepositoryInterface extends RepositoryInterface
{
    public function findById(string $id): ?MonthlySchedule;

    public function findByZoneAndPeriod(string $zoneId, string $periodMonth): ?MonthlySchedule;

    /**
     * @return Collection<int, MonthlySchedule>
     */
    public function getAllByPeriod(string $periodMonth): Collection;

    public function getScheduleWithRecords(string $scheduleId): ?MonthlySchedule;
}
