<?php

namespace App\Services\HR;

use App\Domains\HR\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;

class EmployeeService
{
    public function __construct(
        protected EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function createEmployee(array $data): Employee
    {
        return $this->employeeRepository->create($data);
    }
}
