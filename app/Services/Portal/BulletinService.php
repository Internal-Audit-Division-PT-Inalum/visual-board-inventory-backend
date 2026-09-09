<?php

namespace App\Services\Portal;

use App\Domains\Portal\Models\Bulletin;
use App\Repositories\Contracts\BulletinRepositoryInterface;

class BulletinService
{
    public function __construct(
        protected BulletinRepositoryInterface $bulletinRepository
    ) {}

    public function createBulletin(array $data): Bulletin
    {
        return $this->bulletinRepository->create($data);
    }
}
