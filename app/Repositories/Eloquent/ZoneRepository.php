<?php

namespace App\Repositories\Eloquent;

use App\Domains\VisualBoard\Models\Zone;
use App\Repositories\Contracts\ZoneRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ZoneRepository implements ZoneRepositoryInterface
{
    /**
     * @return Collection<int, Zone>
     */
    public function getAllWithPics(): Collection
    {
        return Zone::with(['picUtama.user', 'picPengganti.user'])->get();
    }

    /**
     * @return Collection<int, Zone>
     */
    public function getAllActive(): Collection
    {
        return Zone::with(['picUtama.user', 'picPengganti.user'])
            ->where('is_active', true)
            ->get();
    }

    public function findById(string $id): ?Zone
    {
        return Zone::find($id);
    }
}
