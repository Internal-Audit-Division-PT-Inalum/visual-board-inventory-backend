<?php

namespace App\Repositories\Eloquent;

use App\Domains\Portal\Models\Bulletin;
use App\Repositories\Contracts\BulletinRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BulletinRepository extends BaseRepository implements BulletinRepositoryInterface
{
    public function __construct(Bulletin $model)
    {
        parent::__construct($model);
    }

    public function getActiveBulletins(int $limit = 10): Collection
    {
        return $this->model->newQuery()
            ->where('is_active', true)
            ->where('published_at', '<=', now())
            ->with(['author:id,name'])
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
