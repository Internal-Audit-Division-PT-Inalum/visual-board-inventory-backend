<?php

namespace App\Repositories\Eloquent;

use App\Domains\Portal\Models\Bulletin;
use App\Repositories\Contracts\BulletinRepositoryInterface;

class BulletinRepository extends BaseRepository implements BulletinRepositoryInterface
{
    public function __construct(Bulletin $model)
    {
        parent::__construct($model);
    }
}
