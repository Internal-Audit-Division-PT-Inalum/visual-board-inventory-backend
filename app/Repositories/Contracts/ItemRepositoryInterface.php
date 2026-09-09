<?php

namespace App\Repositories\Contracts;

use App\Domains\Inventory\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ItemRepositoryInterface
{
    /**
     * Get paginated items with optional filters.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find an item by ID.
     */
    public function findById(string $id): ?Item;

    /**
     * Find an item by SKU.
     */
    public function findBySku(string $sku): ?Item;

    /**
     * Create a new item.
     */
    public function create(array $data): Item;

    /**
     * Update an item.
     */
    public function update(string $id, array $data): Item;

    /**
     * Delete an item (soft delete).
     */
    public function delete(string $id): bool;

    /**
     * Get items with current_stock <= minimum_stock.
     */
    public function getLowStockItems(): Collection;
}
