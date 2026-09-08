<?php

namespace App\Repositories\Eloquent;

use App\Domains\VisualBoard\Models\Abnormality;
use App\Repositories\Contracts\AbnormalityRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AbnormalityRepository implements AbnormalityRepositoryInterface
{
    protected Abnormality $model;

    public function __construct(Abnormality $model)
    {
        $this->model = $model;
    }

    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['zone', 'pic', 'criteria']);

        if (! empty($filters['zone_id'])) {
            $query->where('zone_id', $filters['zone_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_kaizen'])) {
            $query->where('is_kaizen', $filters['is_kaizen']);
        }

        return $query->latest('date_found')->paginate($perPage);
    }

    public function findById(string $id): ?Abnormality
    {
        return $this->model->with(['zone', 'pic', 'criteria', 'monthlySchedule'])->find($id);
    }

    public function create(array $data): Abnormality
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): Abnormality
    {
        $abnormality = $this->model->findOrFail($id);
        $abnormality->update($data);

        return $abnormality->fresh();
    }

    public function delete(string $id): bool
    {
        $abnormality = $this->model->findOrFail($id);

        return $abnormality->delete();
    }
}
