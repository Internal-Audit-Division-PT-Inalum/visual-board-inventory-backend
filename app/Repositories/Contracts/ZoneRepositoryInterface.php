<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ZoneRepositoryInterface
{
    /**
     * Get all zones with their PICs (Utama & Pengganti).
     */
    public function getAllWithPics(): Collection;
}
