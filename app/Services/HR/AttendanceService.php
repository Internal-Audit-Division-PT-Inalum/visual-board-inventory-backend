<?php

namespace App\Services\HR;

use App\Domains\HR\Models\EmployeeAttendance;
use App\Repositories\Contracts\EmployeeAttendanceRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AttendanceService
{
    public function __construct(
        protected EmployeeAttendanceRepositoryInterface $attendanceRepository,
        protected EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function recordAttendance(array $data): EmployeeAttendance
    {
        return $this->attendanceRepository->create($data);
    }

    public function processScan(string $namecode): EmployeeAttendance
    {
        $employee = $this->employeeRepository->findByNamecode($namecode);

        if (! $employee) {
            throw new DomainException('Data pegawai tidak ditemukan. Silakan periksa kembali Kode Pegawai yang Anda masukkan.');
        }

        $today = now()->format('Y-m-d');

        // Cek apakah sudah absen hari ini
        $alreadyScanned = $employee->attendances()->whereDate('date', $today)->exists();

        if ($alreadyScanned) {
            throw new DomainException('Data kehadiran Anda untuk hari ini sudah tercatat sebelumnya. Terima kasih!');
        }

        return $this->recordAttendance([
            'employee_id' => $employee->id,
            'date' => $today,
            'status' => 'present',
        ]);
    }

    /**
     * Get attendance summary for today's Kiosk display.
     * Cached for 5 minutes.
     */
    public function getKioskSummary(): array
    {
        $today = Carbon::today()->format('Y-m-d');

        return Cache::remember("hr:kiosk:attendance_summary:{$today}", 300, function () use ($today) {
            return $this->attendanceRepository->getTodaySummary($today);
        });
    }
}
