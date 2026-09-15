<?php

namespace App\Services\HR;

use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;

class OrganizationService
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function createDepartment(array $data)
    {
        return $this->departmentRepository->create($data);
    }

    public function updateDepartment(string $id, array $data)
    {
        $this->departmentRepository->update($id, $data);

        return $this->departmentRepository->findById($id);
    }

    public function deleteDepartment(string $id): void
    {
        $this->departmentRepository->delete($id);
    }

    public function createEmployee(array $data)
    {
        return $this->employeeRepository->create($data);
    }

    public function updateEmployee(string $id, array $data)
    {
        $this->employeeRepository->update($id, $data);

        return $this->employeeRepository->findById($id);
    }

    public function deleteEmployee(string $id): void
    {
        $this->employeeRepository->delete($id);
    }
}
