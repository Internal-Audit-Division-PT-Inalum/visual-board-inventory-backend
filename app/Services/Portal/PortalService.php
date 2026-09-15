<?php

namespace App\Services\Portal;

use App\Repositories\Contracts\BulletinRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PortalService
{
    public function __construct(
        private BulletinRepositoryInterface $bulletinRepository
    ) {}

    /**
     * Get active bulletins for the Kiosk.
     * Results are cached for 5 minutes to reduce database load from multiple TVs.
     */
    public function getKioskBulletins(int $limit = 10): Collection
    {
        return Cache::remember("portal:kiosk:bulletins:{$limit}", 300, function () use ($limit) {
            return $this->bulletinRepository->getActiveBulletins($limit);
        });
    }
}
