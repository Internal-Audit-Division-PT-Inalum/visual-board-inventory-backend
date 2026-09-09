<?php

namespace App\Repositories\Contracts;

use App\Domains\Inventory\Models\Location;
use Illuminate\Database\Eloquent\Collection;

interface LocationRepositoryInterface
{
    /**
     * Get all active locations.
     */
    public function getAll(): Collection;

    /**
     * Find a location by ID.
     */
    public function findById(string $id): ?Location;

    /**
     * Create a new location.
     */
    public function create(array $data): Location;

    /**
     * Update a location.
     */
    public function update(string $id, array $data): Location;

    /**
     * Delete a location (soft delete).
     */
    public function delete(string $id): bool;
}
