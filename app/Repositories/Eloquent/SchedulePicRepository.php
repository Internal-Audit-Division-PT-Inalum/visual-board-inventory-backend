<?php

namespace App\Repositories\Eloquent;

use App\Domains\VisualBoard\Models\SchedulePic;
use App\Repositories\Contracts\SchedulePicRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<SchedulePic>
 */
class SchedulePicRepository extends BaseRepository implements SchedulePicRepositoryInterface
{
    public function __construct(SchedulePic $model)
    {
        parent::__construct($model);
    }

    /**
     * @return Builder<SchedulePic>
     */
    protected function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * @return Collection<int, SchedulePic>
     */
    public function all(): Collection
    {
        return $this->newQuery()->get();
    }
}
