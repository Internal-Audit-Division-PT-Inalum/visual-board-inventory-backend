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
        $totalEmployees = Employee::count();

        // Hitung total hadir
        $presentCount = $this->model->whereDate('date', $date)
            ->where('status', 'present')
            ->count();

        // Ambil data yang absen (sakit, cuti, dll)
        $absences = $this->model->whereDate('date', $date)
            ->where('status', '!=', 'present')
            ->with(['employee:id,namecode,user_id,position_title', 'employee.user:id,name'])
            ->get();

        $sickCount = $absences->where('status', 'sick')->count();
        $leaveCount = $absences->whereIn('status', ['leave'])->count();

        // Format unavailable_today
        $unavailableToday = $absences->map(function ($attendance) {
            // Map status to leave_type string literal expected by TS
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
                'name' => $attendance->employee->user->name ?? $attendance->employee->namecode,
                'position' => $attendance->employee->position_title ?? 'Staff',
                'leave_type' => $leaveType,
            ];
        })->values()->toArray();

        // Ambil semua data pegawai divisi dan urutkan berdasarkan hierarki
        $employees = Employee::with(['user:id,name', 'department:id,name'])
            ->orderBy('hierarchy_level', 'asc')
            ->orderBy('namecode', 'asc')
            ->get();

        $divisionEmployees = $employees->map(function ($emp) {
            return [
                'user_id' => $emp->user_id,
                'name' => $emp->user->name ?? $emp->namecode,
                'role_label' => $emp->position_title ?? 'Staff',
                'unit' => $emp->department->name ?? 'Divisi IIA',
                'hierarchy_level' => $emp->hierarchy_level,
            ];
        })->values()->toArray();

        return [
            'total_employees' => $totalEmployees,
            'present_count' => $presentCount,
            'on_leave_count' => $leaveCount,
            'sick_count' => $sickCount,
            'unavailable_today' => $unavailableToday,
            'division_employees' => $divisionEmployees,
        ];
    }
}
