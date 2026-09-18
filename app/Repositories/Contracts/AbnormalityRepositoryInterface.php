<?php

namespace App\Repositories\Contracts;

use App\Domains\VisualBoard\Models\Abnormality;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * Get abnormality count summary by status for a given month and year.
     *
     * @return array{open: int, in_progress: int, resolved: int}
     */
    public function getSummaryByMonth(int $month, int $year): array;

    /**
     * Get the latest unresolved abnormalities.
     */
    public function getLatestUnresolved(int $limit = 5): Collection;

    /**
     * Get count of abnormalities resolved today.
     */
    public function getResolvedTodayCount(): int;

    /**
     * Get Kaizen champions (top 3 PICs with most resolved kaizen abnormalities).
     */
    public function getKaizenChampions(): array;

    /**
     * Get Abnormality trend (alert count) for the last N days.
     */
    public function getAbnormalityTrend(int $days = 7): array;

    /**
     * Get monthly trend matrix for a given year, grouped by zone name and month.
     * Menghitung otomatis dari tabel abnormalities sebagai Single Source of Truth.
     * Menggantikan input manual dari tabel trend_abnormalities.
     *
     * @return array<int, array{zone_label: string, months: array<int, array{temuan: int, tindak_lanjut: int, belum_selesai: int}>}>
     */
    public function getMonthlyTrendMatrix(int $year): array;
}
