<?php

namespace App\Repositories\Eloquent;

use App\Domains\VisualBoard\Models\Zone;
use App\Repositories\Contracts\ZoneRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ZoneRepository implements ZoneRepositoryInterface
{
    public function getAllWithPics(): Collection
    {
        return Zone::with(['picUtama:id,name', 'picPengganti:id,name'])->get();
    }
}
