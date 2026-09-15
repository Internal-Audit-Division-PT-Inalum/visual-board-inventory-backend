<?php

namespace App\Repositories\Contracts;

interface EmployeeAttendanceRepositoryInterface extends RepositoryInterface
{
    public function getTodaySummary(string $date): array;
}
