<?php

namespace App\Exports;

use App\Domains\Inventory\Models\InventoryLedger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventoryLedgerExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithTitle
{
    private int $rowNumber = 0;

    public function __construct(
        protected ?string $startDate = null,
        protected ?string $endDate = null,
        protected ?string $type = null,
        protected string $auditorName = 'Auditor Internal',
    ) {}

    /**
     * @return Collection<int, InventoryLedger>
     */
    public function collection(): Collection
    {
        $query = InventoryLedger::query()
            ->with(['item', 'user'])
            ->orderBy('occurred_at', 'desc')
            ->orderBy('created_at', 'desc');

        if ($this->startDate || $this->endDate) {
            $query->where(function (Builder $q): void {
                if ($this->startDate) {
                    $q->where(function (Builder $sub): void {
                        $sub->whereDate('occurred_at', '>=', $this->startDate)
                            ->orWhere(function (Builder $s): void {
                                $s->whereNull('occurred_at')
                                    ->whereDate('created_at', '>=', $this->startDate);
                            });
                    });
                }
                if ($this->endDate) {
                    $q->where(function (Builder $sub): void {
                        $sub->whereDate('occurred_at', '<=', $this->endDate)
                            ->orWhere(function (Builder $s): void {
                                $s->whereNull('occurred_at')
                                    ->whereDate('created_at', '<=', $this->endDate);
                            });
                    });
                }
            });
        }

        if ($this->type && $this->type !== 'all') {
            $query->where('type', $this->type);
        }

        /** @var Collection<int, InventoryLedger> $ledgers */
        $ledgers = $query->get();

        return $ledgers;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function title(): string
    {
        return 'Buku Besar Inventaris';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'NO',
            'WAKTU TRANSAKSI',
            'PETUGAS / OPERATOR',
            'NAMA BARANG',
            'KODE SKU',
            'JENIS MUTASI',
            'KUANTITAS',
            'STOK SEBELUM',
            'STOK SESUDAH',
            'NO. REFERENSI',
            'CATATAN AUDIT TRAIL',
        ];
    }

    /**
     * @param  InventoryLedger  $ledger
     * @return array<int, mixed>
     */
    public function map($ledger): array
    {
        $this->rowNumber++;

        $typeLabel = match ($ledger->type) {
            'in' => 'Barang Masuk (In)',
            'out' => 'Barang Keluar (Out)',
            'borrow' => 'Peminjaman (Borrow)',
            'return' => 'Pengembalian (Return)',
            'adjustment' => 'Koreksi Stok (Adjustment)',
            default => ucfirst((string) $ledger->type),
        };

        $timestamp = $ledger->occurred_at
            ? Carbon::parse($ledger->occurred_at)->translatedFormat('d/m/Y H:i:s')
            : ($ledger->created_at ? Carbon::parse($ledger->created_at)->translatedFormat('d/m/Y H:i:s') : '-');

        return [
            $this->rowNumber,
            $timestamp,
            $ledger->user->name ?? 'Sistem',
            $ledger->item->name ?? '-',
            $ledger->item->sku ?? '-',
            $typeLabel,
            (int) $ledger->quantity,
            (int) $ledger->stock_before,
            (int) $ledger->stock_after,
            $ledger->reference_number ?? '-',
            $ledger->notes ?? '-',
        ];
    }

    /**
     * @return array<string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $totalDataRows = $this->rowNumber;
                $lastDataRow = 5 + $totalDataRows;

                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue('A1', 'PT INDONESIA ASAHAN ALUMINIUM');
                $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E3A8A');

                $sheet->mergeCells('A2:K2');
                $sheet->setCellValue('A2', 'BUKU BESAR MUTASI INVENTARIS (INVENTORY LEDGER AUDIT TRAIL)');
                $sheet->getStyle('A2')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB('0F172A');

                $periodInfo = 'Semua Periode';
                if ($this->startDate && $this->endDate) {
                    $periodInfo = Carbon::parse($this->startDate)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($this->endDate)->translatedFormat('d M Y');
                } elseif ($this->startDate) {
                    $periodInfo = 'Sejak ' . Carbon::parse($this->startDate)->translatedFormat('d M Y');
                } elseif ($this->endDate) {
                    $periodInfo = 'Hingga ' . Carbon::parse($this->endDate)->translatedFormat('d M Y');
                }

                $typeInfo = $this->type && $this->type !== 'all' ? strtoupper($this->type) : 'SEMUA TIPE';

                $sheet->mergeCells('A3:K3');
                $sheet->setCellValue(
                    'A3',
                    "Periode: {$periodInfo} | Filter: {$typeInfo} | Dicetak Pada: " . now()->translatedFormat('d F Y H:i') . " WIB | Oleh: {$this->auditorName}"
                );
                $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('475569');

                $headerRange = 'A5:K5';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 10,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E3A8A'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(28);

                if ($totalDataRows > 0) {
                    $tableRange = 'A5:K' . $lastDataRow;
                    $sheet->getStyle($tableRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CBD5E1'],
                            ],
                        ],
                    ]);

                    $sheet->getStyle('A6:A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('B6:B' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('E6:F' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('G6:I' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('J6:J' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle('G6:I' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0');
                }

                $summaryRow = $lastDataRow + 1;
                $sheet->mergeCells('A' . $summaryRow . ':F' . $summaryRow);
                $sheet->setCellValue('A' . $summaryRow, 'TOTAL TRANSAKSI MUTASI / UNIT');
                if ($totalDataRows > 0) {
                    $sheet->setCellValue('G' . $summaryRow, '=SUM(G6:G' . $lastDataRow . ')');
                } else {
                    $sheet->setCellValue('G' . $summaryRow, 0);
                }
                $sheet->getStyle('A' . $summaryRow . ':K' . $summaryRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1E3A8A']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '1E3A8A']],
                    ],
                ]);
                $sheet->getStyle('A' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('G' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('G' . $summaryRow)->getNumberFormat()->setFormatCode('#,##0');

                $signDateRow = $summaryRow + 3;
                $sheet->setCellValue('A' . $signDateRow, 'Kuala Tanjung, ' . now()->translatedFormat('d F Y'));
                $sheet->getStyle('A' . $signDateRow)->getFont()->setItalic(true);

                $titleSignRow = $signDateRow + 1;
                $sheet->mergeCells('B' . $titleSignRow . ':D' . $titleSignRow);
                $sheet->setCellValue('B' . $titleSignRow, 'Auditor Pemeriksa Mutasi,');
                $sheet->getStyle('B' . $titleSignRow)->getFont()->setBold(true);
                $sheet->getStyle('B' . $titleSignRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('H' . $titleSignRow . ':J' . $titleSignRow);
                $sheet->setCellValue('H' . $titleSignRow, 'Kepala Divisi Internal Audit,');
                $sheet->getStyle('H' . $titleSignRow)->getFont()->setBold(true);
                $sheet->getStyle('H' . $titleSignRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $nameSignRow = $titleSignRow + 4;
                $sheet->mergeCells('B' . $nameSignRow . ':D' . $nameSignRow);
                $sheet->setCellValue('B' . $nameSignRow, '( ' . $this->auditorName . ' )');
                $sheet->getStyle('B' . $nameSignRow)->getFont()->setBold(true);
                $sheet->getStyle('B' . $nameSignRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('H' . $nameSignRow . ':J' . $nameSignRow);
                $sheet->setCellValue('H' . $nameSignRow, '( ................................................ )');
                $sheet->getStyle('H' . $nameSignRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
