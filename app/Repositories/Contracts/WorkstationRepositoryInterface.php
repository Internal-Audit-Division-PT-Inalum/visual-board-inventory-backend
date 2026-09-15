<?php

namespace App\Repositories\Contracts;

use App\Domains\VisualBoard\Models\Workstation;
use Illuminate\Pagination\LengthAwarePaginator;

interface WorkstationRepositoryInterface extends RepositoryInterface
{
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findWithItems(string $id): ?Workstation;
}
