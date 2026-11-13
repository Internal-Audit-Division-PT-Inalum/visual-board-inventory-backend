<?php

namespace App\Repositories\Contracts;

use App\Domains\Inventory\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ItemRepositoryInterface
{
    /**
     * Get paginated items with optional filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Item>
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
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Item;

    /**
     * Update an item.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(string $id, array $data): Item;

    /**
     * Delete an item (soft delete).
     */
    public function delete(string $id): bool;

    /**
     * Get items with current_stock <= minimum_stock.
     *
     * @return Collection<int, Item>
     */
    public function getLowStockItems(): Collection;
}
