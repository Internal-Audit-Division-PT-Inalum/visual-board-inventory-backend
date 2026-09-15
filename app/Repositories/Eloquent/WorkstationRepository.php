<?php

namespace App\Repositories\Eloquent;

use App\Domains\VisualBoard\Models\Workstation;
use App\Repositories\Contracts\WorkstationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkstationRepository extends BaseRepository implements WorkstationRepositoryInterface
{
    public function __construct(Workstation $model)
    {
        parent::__construct($model);
    }

    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where('name', 'ilike', '%' . $filters['search'] . '%');
        }

        if (isset($filters['zone_id'])) {
            $query->where('zone_id', $filters['zone_id']);
        }

        return $query->with(['zone:id,name', 'employee:id,namecode', 'employee.user:id,name'])
            ->latest()
            ->paginate($perPage);
    }

    public function findWithItems(string $id): ?Workstation
    {
        return $this->model->with(['items', 'zone', 'employee', 'employee.user'])->find($id);
    }
}
