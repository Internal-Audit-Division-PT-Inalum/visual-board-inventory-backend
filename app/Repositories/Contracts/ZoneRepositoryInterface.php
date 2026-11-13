<?php

namespace App\Repositories\Contracts;

use App\Domains\VisualBoard\Models\Zone;
use Illuminate\Database\Eloquent\Collection;

interface ZoneRepositoryInterface
{
    /**
     * Get all zones with their PICs (Utama & Pengganti).
     *
     * @return Collection<int, Zone>
     */
    public function getAllWithPics(): Collection;

    /**
     * Get all active zones.
     *
     * @return Collection<int, Zone>
     */
    public function getAllActive(): Collection;

    /**
     * Find a zone by its ID.
     */
    public function findById(string $id): ?Zone;
}
