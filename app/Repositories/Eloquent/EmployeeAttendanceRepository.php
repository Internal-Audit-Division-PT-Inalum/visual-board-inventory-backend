<?php

namespace App\Repositories\Eloquent;

use App\Domains\HR\Models\Employee;
use App\Domains\HR\Models\EmployeeAttendance;
use App\Repositories\Contracts\EmployeeAttendanceRepositoryInterface;

class EmployeeAttendanceRepository extends BaseRepository implements EmployeeAttendanceRepositoryInterface
{
    public function __construct(EmployeeAttendance $model)
    {
        parent::__construct($model);
    }

    public function getTodaySummary(string $date): array
    {
        $totalEmployees = Employee::where('is_active', true)->count();

        $presentCount = $this->model->whereDate('date', $date)
            ->where('status', 'present')
            ->count();

        $absences = $this->model->whereDate('date', $date)
            ->where('status', '!=', 'present')
            ->with(['employee:id,name,namecode,user_id,position_title'])
            ->get();

        $sickCount = $absences->where('status', 'sick')->count();
        $leaveCount = $absences->where('status', 'leave')->count();
        $businessTripCount = $absences->where('status', 'business_trip')->count();

        $unavailableToday = $absences->map(function ($attendance) {
            $leaveType = 'special_leave';
            if ($attendance->status === 'sick') {
                $leaveType = 'sick_leave';
            } elseif ($attendance->status === 'leave') {
                $leaveType = 'annual_leave';
            } elseif ($attendance->status === 'business_trip') {
                $leaveType = 'business_trip';
            }

            return [
                'user_id' => $attendance->employee->user_id,
                'name' => $attendance->employee->name ?? $attendance->employee->namecode,
                'position' => $attendance->employee->position_title ?? 'Staff',
                'leave_type' => $leaveType,
                'avatar_url' => $attendance->employee->getFirstMediaUrl('avatar') ?: null,
            ];
        })->values()->toArray();

        $employees = Employee::with(['user:id,name', 'department:id,name'])
            ->where('is_active', true)
            ->orderBy('hierarchy_level', 'asc')
            ->orderBy('namecode', 'asc')
            ->get();

        $attendancesToday = $this->model->whereDate('date', $date)->get()->keyBy('employee_id');

        $divisionEmployees = $employees->map(function ($emp) use ($attendancesToday) {
            $attendance = $attendancesToday->get($emp->id);

            return [
                'user_id' => $emp->id,
                'name' => $emp->name ?? $emp->namecode,
                'role_label' => $emp->position_title ?? 'Staff',
                'unit' => $emp->department->name ?? 'Divisi IIA',
                'hierarchy_level' => $emp->hierarchy_level,
                'avatar_url' => $emp->getFirstMediaUrl('avatar') ?: null,
                'today_status' => $attendance ? $attendance->status : null,
            ];
        })->values()->toArray();

        return [
            'total_employees' => $totalEmployees,
            'present_count' => $presentCount,
            'on_leave_count' => $leaveCount,
            'sick_count' => $sickCount,
            'business_trip_count' => $businessTripCount,
            'unavailable_today' => $unavailableToday,
            'division_employees' => $divisionEmployees,
        ];
    }
}
