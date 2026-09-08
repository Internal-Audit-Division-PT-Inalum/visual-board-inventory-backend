<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ZoneRepositoryInterface
{
    /**
     * Get all zones with their PICs (Utama & Pengganti).
     *
     * @return Collection
     */
    public function getAllWithPics(): Collection;
}
