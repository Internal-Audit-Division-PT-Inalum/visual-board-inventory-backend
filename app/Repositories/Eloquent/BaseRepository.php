<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 *
 * @implements RepositoryInterface<TModel>
 */
abstract class BaseRepository implements RepositoryInterface
{
    /** @var TModel */
    protected Model $model;

    /**
     * @param  TModel  $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Builder<TModel>
     */
    abstract protected function newQuery(): Builder;

    /**
     * @return Collection<int, TModel>
     */
    abstract public function all(): Collection;

    /**
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->newQuery()->paginate($perPage);
    }

    /**
     * @return TModel|null
     */
    public function findById(string $id): ?Model
    {
        return $this->newQuery()->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return TModel
     */
    public function create(array $data): Model
    {
        return $this->newQuery()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(string $id, array $data): bool
    {
        $record = $this->findById($id);

        if (! $record) {
            return false;
        }

        return $record->update($data);
    }

    public function delete(string $id): bool
    {
        $record = $this->findById($id);

        if (! $record) {
            return false;
        }

        return (bool) $record->delete();
    }
}
