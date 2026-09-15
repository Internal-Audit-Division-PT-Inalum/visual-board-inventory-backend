<?php

namespace App\Repositories\Contracts;

use App\Domains\HR\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;

interface EmployeeRepositoryInterface extends RepositoryInterface
{
    public function findByNamecode(string $namecode): ?Employee;

    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
