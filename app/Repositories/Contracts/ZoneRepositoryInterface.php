<?php

namespace App\Repositories\Contracts;

use App\Domains\VisualBoard\Models\Zone;
use Illuminate\Database\Eloquent\Collection;

interface ZoneRepositoryInterface
{
    /**
     * Get all zones with their PICs (Utama & Pengganti).
     */
    public function getAllWithPics(): Collection;

    /**
     * Get all active zones.
     */
    public function getAllActive(): Collection;

    /**
     * Find a zone by its ID.
     */
    public function findById(string $id): ?Zone;
}
