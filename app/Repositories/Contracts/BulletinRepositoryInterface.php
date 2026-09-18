<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface BulletinRepositoryInterface extends RepositoryInterface
{
    public function getActiveBulletins(int $limit = 10, ?string $type = null): Collection;
}
