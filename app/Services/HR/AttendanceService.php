<?php

namespace App\Services\HR;

use App\Domains\HR\Models\Employee;
use App\Domains\HR\Models\EmployeeAttendance;
use App\Repositories\Contracts\EmployeeAttendanceRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        protected EmployeeAttendanceRepositoryInterface $attendanceRepository,
        protected EmployeeRepositoryInterface $employeeRepository
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordAttendance(array $data): EmployeeAttendance
    {
        return $this->attendanceRepository->create($data);
    }

    public function processScan(string $namecode, string $status = 'present'): EmployeeAttendance
    {
        $employee = $this->employeeRepository->findByNamecode(trim($namecode));

        if (! $employee) {
            throw new DomainException('Data pegawai tidak ditemukan. Silakan periksa kembali Kode Pegawai yang Anda masukkan.');
        }

        $today = now()->format('Y-m-d');

        return DB::transaction(function () use ($employee, $status, $today): EmployeeAttendance {
            $lockedEmployee = Employee::query()
                ->whereKey($employee->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedEmployee || ! $lockedEmployee->is_active) {
                throw new DomainException('Data pegawai tidak ditemukan atau status pegawai tidak aktif.');
            }

            $alreadyScanned = $lockedEmployee->attendances()
                ->whereDate('date', $today)
                ->exists();

            if ($alreadyScanned) {
                throw new DomainException('Data kehadiran Anda untuk hari ini sudah tercatat sebelumnya. Terima kasih!');
            }

            try {
                $attendance = $this->recordAttendance([
                    'employee_id' => $lockedEmployee->id,
                    'date' => $today,
                    'status' => $status,
                ]);
            } catch (UniqueConstraintViolationException) {
                throw new DomainException('Data kehadiran Anda untuk hari ini sudah tercatat sebelumnya. Terima kasih!');
            }

            Cache::forget("hr:kiosk:attendance_summary:{$today}");

            return $attendance;
        });
    }

    /**
     * Get attendance summary for today's Kiosk display.
     * Cached for 5 minutes.
     *
     * @return array<string, mixed>
     */
    public function getKioskSummary(): array
    {
        $today = Carbon::today()->format('Y-m-d');

        return Cache::remember("hr:kiosk:attendance_summary:{$today}", 300, function () use ($today) {
            return $this->attendanceRepository->getTodaySummary($today);
        });
    }
}
