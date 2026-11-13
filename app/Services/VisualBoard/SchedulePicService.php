<?php

namespace App\Services\VisualBoard;

use App\Domains\VisualBoard\Models\SchedulePic;
use App\Repositories\Contracts\SchedulePicRepositoryInterface;

class SchedulePicService
{
    public function __construct(
        protected SchedulePicRepositoryInterface $schedulePicRepository
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function assignPic(array $data): SchedulePic
    {
        return $this->schedulePicRepository->create($data);
    }
}
