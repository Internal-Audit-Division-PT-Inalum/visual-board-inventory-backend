<?php

namespace App\Repositories\Eloquent;

use App\Domains\Inventory\Models\Location;
use App\Repositories\Contracts\LocationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LocationRepository implements LocationRepositoryInterface
{
    public function __construct(protected Location $model) {}

    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->withCount('items')
            ->orderBy('name')
            ->get();
    }

    public function findById(string $id): ?Location
    {
        return $this->model->newQuery()
            ->withCount('items')
            ->find($id);
    }

    public function create(array $data): Location
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): Location
    {
        $location = $this->model->findOrFail($id);
        $location->update($data);

        return $location->fresh();
    }

    public function delete(string $id): bool
    {
        $location = $this->model->findOrFail($id);

        return $location->delete();
    }
}
