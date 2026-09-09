<?php

namespace App\Repositories\Eloquent;

use App\Domains\HR\Models\EmployeeAttendance;
use App\Repositories\Contracts\EmployeeAttendanceRepositoryInterface;

class EmployeeAttendanceRepository extends BaseRepository implements EmployeeAttendanceRepositoryInterface
{
    public function __construct(EmployeeAttendance $model)
    {
        parent::__construct($model);
    }
}
