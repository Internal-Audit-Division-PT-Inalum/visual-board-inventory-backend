<?php

namespace App\Services\Portal;

use App\Repositories\Contracts\BulletinRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PortalService
{
    public function __construct(
        private BulletinRepositoryInterface $bulletinRepository
    ) {}

    /**
     * Get active bulletins for the Kiosk.
     * Results are cached for 5 minutes to reduce database load from multiple TVs.
     */
    public function getKioskBulletins(int $limit = 10, ?string $type = null): Collection
    {
        return $this->bulletinRepository->getActiveBulletins($limit, $type);
    }
}
