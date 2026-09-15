<?php

namespace App\Repositories\Eloquent;

use App\Domains\HR\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{
    public function __construct(Employee $model)
    {
        parent::__construct($model);
    }

    public function findByNamecode(string $namecode): ?Employee
    {
        return $this->model->where('namecode', $namecode)->first();
    }

    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('namecode', 'ilike', '%' . $filters['search'] . '%')
                    ->orWhere('position_title', 'ilike', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        return $query->with(['user:id,name,email', 'department:id,name'])
            ->latest()
            ->paginate($perPage);
    }
}
