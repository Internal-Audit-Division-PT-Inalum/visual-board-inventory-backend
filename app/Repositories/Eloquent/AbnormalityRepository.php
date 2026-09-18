<?php

namespace App\Repositories\Eloquent;

use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Models\Zone;
use App\Repositories\Contracts\AbnormalityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AbnormalityRepository implements AbnormalityRepositoryInterface
{
    protected Abnormality $model;

    public function __construct(Abnormality $model)
    {
        $this->model = $model;
    }

    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['zone', 'pic', 'criteria']);

        if (! empty($filters['zone_id'])) {
            $query->where('zone_id', $filters['zone_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_kaizen'])) {
            $query->where('is_kaizen', $filters['is_kaizen']);
        }

        return $query->latest('date_found')->paginate($perPage);
    }

    public function findById(string $id): ?Abnormality
    {
        return $this->model->with(['zone', 'pic', 'criteria', 'monthlySchedule'])->find($id);
    }

    public function create(array $data): Abnormality
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): Abnormality
    {
        $abnormality = $this->model->findOrFail($id);
        $abnormality->update($data);

        return $abnormality->fresh();
    }

    public function delete(string $id): bool
    {
        $abnormality = $this->model->findOrFail($id);

        return $abnormality->delete();
    }

    public function getSummaryByMonth(int $month, int $year): array
    {
        $data = $this->model->newQuery()
            ->whereMonth('date_found', $month)
            ->whereYear('date_found', $year)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'open' => $data['open'] ?? 0,
            'in_progress' => $data['in_progress'] ?? 0,
            'resolved' => $data['resolved'] ?? 0,
        ];
    }

    public function getLatestUnresolved(int $limit = 5): Collection
    {
        return $this->model->newQuery()
            ->with(['zone', 'pic'])
            ->whereIn('status', ['open', 'in_progress'])
            ->latest('date_found')
            ->limit($limit)
            ->get();
    }

    public function getResolvedTodayCount(): int
    {
        return $this->model->newQuery()
            ->where('status', 'resolved')
            ->whereDate('updated_at', Carbon::today())
            ->count();
    }

    public function getKaizenChampions(): array
    {
        // Mendapatkan 3 PIC dengan jumlah resolved kaizen abnormality terbanyak
        $champions = $this->model->newQuery()
            ->select('pic_id', DB::raw('count(*) as kaizen_count'))
            ->where('is_kaizen', true)
            ->where('status', 'resolved')
            ->whereNotNull('pic_id')
            ->groupBy('pic_id')
            ->orderByDesc('kaizen_count')
            ->limit(3)
            ->with('pic.department') // Asumsikan user (pic) memiliki relasi department, jika ada
            ->get();

        $result = [];
        $rank = 1;
        foreach ($champions as $champion) {
            $user = $champion->pic;
            if ($user) {
                // Di sistem ini kita buat skor seolah kaizen_count * 15
                $score = $champion->kaizen_count * 15;
                // Untuk demo mock department jika relasi tidak ada
                $department = $user->department?->name ?? 'Produksi';

                $result[] = [
                    'rank' => $rank++,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'department' => $department,
                    'score' => $score,
                    'kaizen_count' => $champion->kaizen_count,
                ];
            }
        }

        return $result;
    }

    public function getAbnormalityTrend(int $days = 7): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        $trends = $this->model->newQuery()
            ->select(DB::raw('DATE(date_found) as date'), DB::raw('count(*) as count'))
            ->where('date_found', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            // Format hari: 'Senin', 'Selasa', dll.
            $dayName = $date->locale('id')->isoFormat('dddd');

            $result[] = [
                'day' => $dayName,
                'alert_count' => $trends->has($dateStr) ? $trends->get($dateStr)->count : 0,
            ];
        }

        return $result;
    }

    public function getMonthlyTrendMatrix(int $year): array
    {
        // Ambil semua zona yang aktif, diurutkan berdasarkan nama
        $zones = Zone::orderBy('name')->get(['id', 'name']);

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $monthSelect = $isSqlite ? "CAST(strftime('%m', date_found) AS INTEGER) as month" : 'EXTRACT(MONTH FROM date_found)::integer as month';
        $monthGroup = $isSqlite ? "strftime('%m', date_found)" : 'EXTRACT(MONTH FROM date_found)';

        // Kalkulasi agregat langsung dari tabel abnormalities (Single Source of Truth)
        $aggregates = $this->model->newQuery()
            ->select(
                'zone_id',
                DB::raw($monthSelect),
                DB::raw('COUNT(*) as temuan'),
                DB::raw('SUM(CASE WHEN status = \'resolved\' THEN 1 ELSE 0 END) as tindak_lanjut'),
                DB::raw('SUM(CASE WHEN status IN (\'open\', \'in_progress\') THEN 1 ELSE 0 END) as belum_selesai')
            )
            ->whereYear('date_found', $year)
            ->groupBy('zone_id', DB::raw($monthGroup))
            ->orderBy(DB::raw($monthGroup))
            ->get()
            ->groupBy('zone_id');

        $months = range(1, 12);
        $matrix = [];

        // Susun matriks per zona per bulan
        foreach ($zones as $zone) {
            $zoneData = $aggregates->get($zone->id, collect());
            $row = ['zone_label' => $zone->name, 'months' => []];

            foreach ($months as $m) {
                $record = $zoneData->firstWhere('month', $m);
                $row['months'][$m] = [
                    'temuan' => $record ? (int) $record->temuan : null,
                    'tindak_lanjut' => $record ? (int) $record->tindak_lanjut : null,
                    'belum_selesai' => $record ? (int) $record->belum_selesai : null,
                ];
            }

            $matrix[] = $row;
        }

        // Tambahkan baris Total (agregasi seluruh zona)
        $allAggregates = $this->model->newQuery()
            ->select(
                DB::raw($monthSelect),
                DB::raw('COUNT(*) as temuan'),
                DB::raw('SUM(CASE WHEN status = \'resolved\' THEN 1 ELSE 0 END) as tindak_lanjut'),
                DB::raw('SUM(CASE WHEN status IN (\'open\', \'in_progress\') THEN 1 ELSE 0 END) as belum_selesai')
            )
            ->whereYear('date_found', $year)
            ->groupBy(DB::raw($monthGroup))
            ->orderBy(DB::raw($monthGroup))
            ->get()
            ->keyBy('month');

        $totalRow = ['zone_label' => 'Total', 'months' => []];
        foreach ($months as $m) {
            $total = $allAggregates->get($m);
            $totalRow['months'][$m] = [
                'temuan' => $total ? (int) $total->temuan : null,
                'tindak_lanjut' => $total ? (int) $total->tindak_lanjut : null,
                'belum_selesai' => $total ? (int) $total->belum_selesai : null,
            ];
        }
        $matrix[] = $totalRow;

        return $matrix;
    }
}
