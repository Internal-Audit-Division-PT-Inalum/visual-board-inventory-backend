<?php

namespace App\Repositories\Contracts;

use App\Domains\HR\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<Employee>
 */
interface EmployeeRepositoryInterface extends RepositoryInterface
{
    public function findByNamecode(string $namecode): ?Employee;

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Employee>
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
