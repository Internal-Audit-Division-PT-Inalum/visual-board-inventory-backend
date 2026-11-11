<?php

namespace App\Repositories\Contracts;

use App\Domains\VisualBoard\Models\MonthlySchedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface MonthlyScheduleRepositoryInterface extends RepositoryInterface
{
    public function findById(string $id): ?MonthlySchedule;

    public function findByZoneAndPeriod(string $zoneId, string $periodMonth): ?Model;

    /**
     * @return Collection
     */
    public function getAllByPeriod(string $periodMonth);

    public function getScheduleWithRecords(string $scheduleId): ?Model;
}
