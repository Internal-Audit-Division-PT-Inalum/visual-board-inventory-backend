<?php

namespace App\Repositories\Eloquent;

use App\Domains\Inventory\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemRepository implements ItemRepositoryInterface
{
    public function __construct(protected Item $model) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Item>
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['location']);

        if (! empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(string $id): ?Item
    {
        return $this->model->with(['location'])->where(function ($q) use ($id) {
            $q->where('id', $id)->orWhere('sku', $id);
        })->first();
    }

    public function findBySku(string $sku): ?Item
    {
        return $this->model->with(['location'])->where('sku', $sku)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Item
    {
        return $this->model->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(string $id, array $data): Item
    {
        $item = $this->model->findOrFail($id);
        $item->update($data);

        return $item->fresh(['location']);
    }

    public function delete(string $id): bool
    {
        $item = $this->model->findOrFail($id);

        return (bool) $item->delete();
    }

    /**
     * @return Collection<int, Item>
     */
    public function getLowStockItems(): Collection
    {
        return $this->model->newQuery()
            ->with(['location'])
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('is_active', true)
            ->orderBy('current_stock')
            ->get();
    }
}
