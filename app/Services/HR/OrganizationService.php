<?php

namespace App\Services\HR;

use App\Domains\HR\Models\Department;
use App\Domains\HR\Models\Employee;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;

class OrganizationService
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDepartment(array $data): Department
    {
        return $this->departmentRepository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDepartment(string $id, array $data): ?Department
    {
        $this->departmentRepository->update($id, $data);

        return $this->departmentRepository->findById($id);
    }

    public function deleteDepartment(string $id): void
    {
        $this->departmentRepository->delete($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createEmployee(array $data): Employee
    {
        return $this->employeeRepository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateEmployee(string $id, array $data): ?Employee
    {
        $this->employeeRepository->update($id, $data);

        return $this->employeeRepository->findById($id);
    }

    public function deleteEmployee(string $id): void
    {
        $this->employeeRepository->delete($id);
    }
}
