<?php

namespace App\Repositories\Contracts;

use App\Domains\VisualBoard\Models\Abnormality;
use Illuminate\Pagination\LengthAwarePaginator;

interface AbnormalityRepositoryInterface
{
    /**
     * Get paginated abnormalities with optional filters.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find an abnormality by ID.
     */
    public function findById(string $id): ?Abnormality;

    /**
     * Create a new abnormality.
     */
    public function create(array $data): Abnormality;

    /**
     * Update an abnormality.
     */
    public function update(string $id, array $data): Abnormality;

    /**
     * Delete an abnormality.
     */
    public function delete(string $id): bool;
}
