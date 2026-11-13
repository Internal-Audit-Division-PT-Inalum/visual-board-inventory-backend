<?php

namespace App\Repositories\Contracts;

use App\Domains\VisualBoard\Models\Workstation;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<Workstation>
 */
interface WorkstationRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Workstation>
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findWithItems(string $id): ?Workstation;

    public function findByEmployeeWithItems(string $userId): ?Workstation;
}
