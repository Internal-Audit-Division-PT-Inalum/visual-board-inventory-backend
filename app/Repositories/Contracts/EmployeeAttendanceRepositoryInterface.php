<?php

namespace App\Repositories\Contracts;

use App\Domains\HR\Models\EmployeeAttendance;

/**
 * @extends RepositoryInterface<EmployeeAttendance>
 */
interface EmployeeAttendanceRepositoryInterface extends RepositoryInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getTodaySummary(string $date): array;
}
