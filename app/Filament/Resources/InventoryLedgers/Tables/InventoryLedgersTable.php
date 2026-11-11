<?php

namespace App\Filament\Resources\InventoryLedgers\Tables;

use App\Domains\Inventory\Models\InventoryLedger;
use App\Exports\InventoryLedgerExport;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InventoryLedgersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Waktu Kejadian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(fn (InventoryLedger $record): ?string => $record->created_at?->translatedFormat('d/m/Y H:i')),
                TextColumn::make('item.name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('item.sku')
                    ->label('Kode SKU')
                    ->searchable()
                    ->sortable()
                    ->color('gray'),
                TextColumn::make('user.name')
                    ->label('Petugas / Operator')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Jenis Mutasi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Masuk (In)',
                        'out' => 'Keluar (Out)',
                        'borrow' => 'Pinjam (Borrow)',
                        'return' => 'Kembali (Return)',
                        'adjustment' => 'Koreksi (Adjustment)',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        'borrow' => 'warning',
                        'return' => 'info',
                        'adjustment' => 'gray',
                        default => 'secondary',
                    })
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_before')
                    ->label('Stok Sebelum')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stock_after')
                    ->label('Stok Sesudah')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reference_number')
                    ->label('No. Referensi')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('notes')
                    ->label('Catatan Audit')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Tercatat Di Sistem')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis Mutasi')
                    ->options([
                        'in' => 'Barang Masuk (In)',
                        'out' => 'Barang Keluar (Out)',
                        'borrow' => 'Peminjaman Aset (Borrow)',
                        'return' => 'Pengembalian Aset (Return)',
                        'adjustment' => 'Koreksi Stok (Adjustment)',
                    ]),
                Filter::make('occurred_at')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $q, string $date): Builder => $q->where(function (Builder $sub) use ($date): void {
                                    $sub->whereDate('occurred_at', '>=', $date)
                                        ->orWhere(function (Builder $s) use ($date): void {
                                            $s->whereNull('occurred_at')
                                                ->whereDate('created_at', '>=', $date);
                                        });
                                })
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $q, string $date): Builder => $q->where(function (Builder $sub) use ($date): void {
                                    $sub->whereDate('occurred_at', '<=', $date)
                                        ->orWhere(function (Builder $s) use ($date): void {
                                            $s->whereNull('occurred_at')
                                                ->whereDate('created_at', '<=', $date);
                                        });
                                })
                            );
                    }),
            ])
            ->headerActions([
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
            ]);
    }
}
