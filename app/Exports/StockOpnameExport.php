<?php

namespace App\Exports;

use App\Domains\Inventory\Models\Item;
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

class StockOpnameExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithTitle
{
    private int $rowNumber = 0;

    public function __construct(
        protected string $auditorName = 'Auditor Internal',
        protected ?string $type = null,
    ) {}

    /**
     * @return Collection<int, Item>
     */
    public function collection(): Collection
    {
        $query = Item::query()
            ->with(['location'])
            ->where('is_active', true)
            ->orderBy('name', 'asc');

        if ($this->type) {
            $query->where('type', $this->type);
        }

        /** @var Collection<int, Item> $items */
        $items = $query->get();

        return $items;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function title(): string
    {
        return 'Stock Opname Fisik';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'NO',
            'KODE SKU',
            'NAMA BARANG',
            'TIPE / KATEGORI',
            'LOKASI RAK',
            'SATUAN',
            'STOK SISTEM',
            'HITUNG FISIK (OPNAME)',
            'SELISIH',
            'KETERANGAN AUDIT',
        ];
    }

    /**
     * @param  Item  $item
     * @return array<int, mixed>
     */
    public function map($item): array
    {
        $this->rowNumber++;
        $excelRow = 5 + $this->rowNumber;

        $typeLabel = match ($item->type) {
            'asset' => 'Aset Tetap',
            'consumable' => 'Konsumabel',
            default => ucfirst((string) $item->type),
        };

        return [
            $this->rowNumber,
            $item->sku,
            $item->name,
            $typeLabel,
            $item->location->name ?? '-',
            $item->unit,
            (int) $item->current_stock,
            '',
            '=IF(ISBLANK(H' . $excelRow . '),"",H' . $excelRow . '-G' . $excelRow . ')',
            '',
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

                $sheet->mergeCells('A1:J1');
                $sheet->setCellValue('A1', 'PT INDONESIA ASAHAN ALUMINIUM');
                $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E3A8A');

                $sheet->mergeCells('A2:J2');
                $sheet->setCellValue('A2', 'BERITA ACARA STOCK OPNAME FISIK LOGISTIK & ASET');
                $sheet->getStyle('A2')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB('0F172A');

                $sheet->mergeCells('A3:J3');
                $sheet->setCellValue(
                    'A3',
                    'Divisi Internal Audit | Tanggal Penarikan Data: ' . now()->translatedFormat('d F Y H:i') . ' WIB | Auditor: ' . $this->auditorName
                );
                $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('475569');

                $headerRange = 'A5:J5';
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
                    $tableRange = 'A5:J' . $lastDataRow;
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
                    $sheet->getStyle('D6:D' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('F6:F' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('G6:G' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('H6:H' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('I6:I' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getStyle('G6:H' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle('I6:I' . $lastDataRow)->getNumberFormat()->setFormatCode('+#,##0;-#,##0;0');
                }

                $summaryRow = $lastDataRow + 1;
                $sheet->mergeCells('A' . $summaryRow . ':F' . $summaryRow);
                $sheet->setCellValue('A' . $summaryRow, 'TOTAL KUANTITAS STOK SISTEM');
                if ($totalDataRows > 0) {
                    $sheet->setCellValue('G' . $summaryRow, '=SUM(G6:G' . $lastDataRow . ')');
                } else {
                    $sheet->setCellValue('G' . $summaryRow, 0);
                }
                $sheet->getStyle('A' . $summaryRow . ':J' . $summaryRow)->applyFromArray([
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
                $sheet->mergeCells('B' . $titleSignRow . ':C' . $titleSignRow);
                $sheet->setCellValue('B' . $titleSignRow, 'Auditor Pelaksana,');
                $sheet->getStyle('B' . $titleSignRow)->getFont()->setBold(true);
                $sheet->getStyle('B' . $titleSignRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('E' . $titleSignRow . ':F' . $titleSignRow);
                $sheet->setCellValue('E' . $titleSignRow, 'Pengelola Gudang & Logistik,');
                $sheet->getStyle('E' . $titleSignRow)->getFont()->setBold(true);
                $sheet->getStyle('E' . $titleSignRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('H' . $titleSignRow . ':I' . $titleSignRow);
                $sheet->setCellValue('H' . $titleSignRow, 'Kepala Divisi Internal Audit,');
                $sheet->getStyle('H' . $titleSignRow)->getFont()->setBold(true);
                $sheet->getStyle('H' . $titleSignRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $nameSignRow = $titleSignRow + 4;
                $sheet->mergeCells('B' . $nameSignRow . ':C' . $nameSignRow);
                $sheet->setCellValue('B' . $nameSignRow, '( ' . $this->auditorName . ' )');
                $sheet->getStyle('B' . $nameSignRow)->getFont()->setBold(true);
                $sheet->getStyle('B' . $nameSignRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('E' . $nameSignRow . ':F' . $nameSignRow);
                $sheet->setCellValue('E' . $nameSignRow, '( ................................................ )');
                $sheet->getStyle('E' . $nameSignRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('H' . $nameSignRow . ':I' . $nameSignRow);
                $sheet->setCellValue('H' . $nameSignRow, '( ................................................ )');
                $sheet->getStyle('H' . $nameSignRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
