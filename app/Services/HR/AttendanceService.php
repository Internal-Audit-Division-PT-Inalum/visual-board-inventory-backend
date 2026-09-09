<?php

namespace App\Services\HR;

use App\Domains\HR\Models\EmployeeAttendance;
use App\Repositories\Contracts\EmployeeAttendanceRepositoryInterface;

class AttendanceService
{
    public function __construct(
        protected EmployeeAttendanceRepositoryInterface $attendanceRepository
    ) {}

    public function recordAttendance(array $data): EmployeeAttendance
    {
        return $this->attendanceRepository->create($data);
    }
}
