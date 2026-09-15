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

    public function getTodaySummary(string $date): array
    {
        // Hitung total hadir
        $presentCount = $this->model->whereDate('date', $date)
            ->where('status', 'hadir')
            ->count();

        // Ambil data yang absen (sakit, cuti, dll)
        $absences = $this->model->whereDate('date', $date)
            ->where('status', '!=', 'hadir')
            ->with(['employee:id,namecode,user_id', 'employee.user:id,name'])
            ->get();

        $sickCount = $absences->where('status', 'sakit')->count();
        $leaveCount = $absences->whereIn('status', ['cuti_tahunan', 'cuti_alasan_penting', 'cuti_besar'])->count();
        $otherCount = $absences->whereNotIn('status', ['hadir', 'sakit', 'cuti_tahunan', 'cuti_alasan_penting', 'cuti_besar'])->count();

        return [
            'counts' => [
                'present' => $presentCount,
                'sick' => $sickCount,
                'leave' => $leaveCount,
                'other' => $otherCount,
            ],
            'unavailable_employees' => $absences,
        ];
    }
}
