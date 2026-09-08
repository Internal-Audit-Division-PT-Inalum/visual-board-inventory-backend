<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface MonthlyScheduleRepositoryInterface extends RepositoryInterface
{
    public function findByZoneAndPeriod(string $zoneId, string $periodMonth): ?Model;

    public function getScheduleWithRecords(string $scheduleId): ?Model;
}
