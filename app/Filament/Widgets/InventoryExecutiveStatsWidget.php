<?php

namespace App\Filament\Widgets;

use App\Domains\Inventory\Models\AssetLoan;
use App\Domains\Inventory\Models\Item;
use App\Filament\Resources\AssetLoans\AssetLoanResource;
use App\Filament\Resources\Items\ItemResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class InventoryExecutiveStatsWidget extends BaseWidget
{
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalKatalog = Item::where('is_active', true)->count();
        $stokKritis = Item::where('is_active', true)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->count();
        $asetDipinjam = (int) AssetLoan::whereNull('fully_returned_at')
            ->sum(DB::raw('quantity - returned_quantity'));
        $overdueLoans = AssetLoan::whereNull('fully_returned_at')
            ->where('borrowed_at', '<=', now()->subDays(7))
            ->count();

        return [
            Stat::make('Total Katalog Barang', number_format($totalKatalog, 0, ',', '.'))
                ->description('Barang aktif dalam sistem')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->url(ItemResource::getUrl('index'))
                ->extraAttributes([
                    'class' => 'border-t-4 border-blue-500 shadow-sm bg-white dark:bg-gray-900',
                ]),

            Stat::make('Barang Stok Kritis', number_format($stokKritis, 0, ',', '.'))
                ->description('Perlu penambahan stok segera')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->url(ItemResource::getUrl('index', ['tableFilters[critical_stock][value]' => '1']))
                ->extraAttributes([
                    'class' => 'border-t-4 border-rose-500 shadow-sm bg-white dark:bg-gray-900',
                ]),

            Stat::make('Aset Sedang Dipinjam', number_format($asetDipinjam, 0, ',', '.') . ' Unit')
                ->description('Unit alat kerja di luar gudang')
                ->descriptionIcon('heroicon-m-arrow-up-right')
                ->color('warning')
                ->url(AssetLoanResource::getUrl('index'))
                ->extraAttributes([
                    'class' => 'border-t-4 border-amber-500 shadow-sm bg-white dark:bg-gray-900',
                ]),

            Stat::make('Pinjaman Overdue (>= 7 Hari)', number_format($overdueLoans, 0, ',', '.') . ' Transaksi')
                ->description('Belum kembali melampaui batas')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger')
                ->url(AssetLoanResource::getUrl('index', ['tableFilters[status][value]' => 'overdue']))
                ->extraAttributes([
                    'class' => 'border-t-4 border-red-600 shadow-sm bg-white dark:bg-gray-900',
                ]),
        ];
    }
}
