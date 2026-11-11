<?php

namespace App\Filament\Resources\InventoryLedgers\Pages;

use App\Exports\InventoryLedgerExport;
use App\Filament\Resources\InventoryLedgers\InventoryLedgerResource;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListInventoryLedgers extends ListRecords
{
    protected static string $resource = InventoryLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_ledger')
                ->label('Ekspor Buku Besar')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Dari Tanggal')
                        ->default(now()->startOfMonth()),
                    DatePicker::make('end_date')
                        ->label('Sampai Tanggal')
                        ->default(now()),
                    Select::make('type')
                        ->label('Jenis Mutasi')
                        ->options([
                            'all' => 'Semua Jenis Mutasi',
                            'in' => 'Barang Masuk (In)',
                            'out' => 'Barang Keluar (Out)',
                            'borrow' => 'Peminjaman Aset (Borrow)',
                            'return' => 'Pengembalian Aset (Return)',
                            'adjustment' => 'Koreksi Stok (Adjustment)',
                        ])
                        ->default('all'),
                ])
                ->modalHeading('Ekspor Buku Besar Inventaris (Excel)')
                ->modalDescription('Unduh laporan audit trail mutasi inventaris berstandar BUMN dalam format berkas Excel (.xlsx).')
                ->modalSubmitActionLabel('Unduh Excel')
                ->action(function (array $data): BinaryFileResponse {
                    $auditorName = auth()->user()->name ?? 'Auditor Internal';
                    $startDate = ! empty($data['start_date']) ? (string) $data['start_date'] : null;
                    $endDate = ! empty($data['end_date']) ? (string) $data['end_date'] : null;
                    $type = (! empty($data['type']) && $data['type'] !== 'all') ? (string) $data['type'] : null;

                    $filename = 'Buku_Besar_Inventaris_' . now()->format('Ymd_His') . '.xlsx';

                    /** @var BinaryFileResponse $response */
                    $response = Excel::download(
                        new InventoryLedgerExport(
                            startDate: $startDate,
                            endDate: $endDate,
                            type: $type,
                            auditorName: $auditorName,
                        ),
                        $filename
                    );

                    return $response;
                }),
        ];
    }
}
