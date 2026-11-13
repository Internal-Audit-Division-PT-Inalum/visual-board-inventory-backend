<?php

namespace App\Repositories\Eloquent;

use App\Domains\HR\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepository<Employee>
 */
class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{
    public function __construct(Employee $model)
    {
        parent::__construct($model);
    }

    /**
     * @return Builder<Employee>
     */
    protected function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * @return Collection<int, Employee>
     */
    public function all(): Collection
    {
        return $this->newQuery()->get();
    }

    public function findByNamecode(string $namecode): ?Employee
    {
        return $this->model->newQuery()->where('namecode', $namecode)->first();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Employee>
     */
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
