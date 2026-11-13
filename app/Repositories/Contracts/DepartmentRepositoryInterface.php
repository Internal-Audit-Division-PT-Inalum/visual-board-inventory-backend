<?php

namespace App\Repositories\Contracts;

use App\Domains\HR\Models\Department;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<Department>
 */
interface DepartmentRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Department>
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
