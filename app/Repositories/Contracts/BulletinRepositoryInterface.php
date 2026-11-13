<?php

namespace App\Repositories\Contracts;

use App\Domains\Portal\Models\Bulletin;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends RepositoryInterface<Bulletin>
 */
interface BulletinRepositoryInterface extends RepositoryInterface
{
    /**
     * @return Collection<int, Bulletin>
     */
    public function getActiveBulletins(int $limit = 10, ?string $type = null): Collection;
}
