<?php

namespace App\Services\HR;

use App\Domains\HR\Models\Department;
use App\Repositories\Contracts\DepartmentRepositoryInterface;

class DepartmentService
{
    public function __construct(
        protected DepartmentRepositoryInterface $departmentRepository
    ) {}

    public function createDepartment(array $data): Department
    {
        return $this->departmentRepository->create($data);
    }
}
