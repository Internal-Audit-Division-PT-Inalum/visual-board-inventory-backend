<?php

namespace App\Repositories\Eloquent;

use App\Domains\VisualBoard\Models\SchedulePic;
use App\Repositories\Contracts\SchedulePicRepositoryInterface;

class SchedulePicRepository extends BaseRepository implements SchedulePicRepositoryInterface
{
    public function __construct(SchedulePic $model)
    {
        parent::__construct($model);
    }
}
