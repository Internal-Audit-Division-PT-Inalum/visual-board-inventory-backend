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

    public function getActiveBulletins(int $limit = 10, ?string $type = null): Collection
    {
        $query = $this->model->newQuery()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });

        if ($type) {
            // Mapping for the frontend category
            if ($type === 'general') {
                $query->whereIn('type', ['general', 'event', 'policy']);
            } elseif ($type === 'health') {
                $query->where('type', 'health_safety');
            } else {
                $query->where('type', $type);
            }
        }

        return $query->with(['author:id,name'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
