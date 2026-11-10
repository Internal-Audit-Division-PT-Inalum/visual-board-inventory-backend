<?php

namespace App\Filament\Resources\AssetLoans\Tables;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\AssetLoan;
use App\Services\Inventory\InventoryService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AssetLoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('borrowed_at', 'desc')
            ->columns([
                TextColumn::make('item.name')
                    ->label('Nama Alat')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (AssetLoan $record): string => $record->item !== null ? "SKU: {$record->item->sku}" : ''),

                TextColumn::make('employee.name')
                    ->label('Peminjam')
                    ->searchable()
                    ->sortable()
                    ->description(fn (AssetLoan $record): string => $record->employee !== null ? "NIK: {$record->employee->namecode}" : ''),

                TextColumn::make('quantity')
                    ->label('Dipinjam')
                    ->numeric()
                    ->sortable()
                    ->suffix(fn (AssetLoan $record): string => $record->item !== null ? " {$record->item->unit}" : ' unit'),

                TextColumn::make('returned_quantity')
                    ->label('Kembali')
                    ->numeric()
                    ->sortable()
                    ->suffix(fn (AssetLoan $record): string => $record->item !== null ? " {$record->item->unit}" : ' unit'),

                TextColumn::make('outstanding')
                    ->label('Sisa Pinjam')
                    ->state(fn (AssetLoan $record): int => max(0, $record->quantity - $record->returned_quantity))
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'success' : 'danger')
                    ->suffix(fn (AssetLoan $record): string => $record->item !== null ? " {$record->item->unit}" : ' unit'),

                TextColumn::make('borrowed_at')
                    ->label('Waktu Pinjam')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(function (AssetLoan $record): string {
                        if ($record->fully_returned_at !== null || $record->returned_quantity >= $record->quantity) {
                            return 'Lunas';
                        }

                        if ($record->borrowed_at->lte(now()->subDays(7))) {
                            return 'Overdue';
                        }

                        if ($record->returned_quantity > 0) {
                            return 'Sebagian';
                        }

                        return 'Aktif';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Overdue' => 'danger',
                        'Sebagian' => 'info',
                        default => 'warning',
                    }),

                TextColumn::make('fully_returned_at')
                    ->label('Waktu Lunas')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Pinjaman')
                    ->options([
                        'active' => 'Aktif (Belum Kembali)',
                        'overdue' => 'Overdue (>= 7 Hari)',
                        'partial' => 'Sebagian Kembali',
                        'settled' => 'Lunas / Selesai',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'active' => $query->whereNull('fully_returned_at')
                                ->where('returned_quantity', 0)
                                ->where('borrowed_at', '>', now()->subDays(7)),
                            'overdue' => $query->whereNull('fully_returned_at')
                                ->where('returned_quantity', '<', DB::raw('quantity'))
                                ->where('borrowed_at', '<=', now()->subDays(7)),
                            'partial' => $query->whereNull('fully_returned_at')
                                ->where('returned_quantity', '>', 0),
                            'settled' => $query->where(function (Builder $sub) {
                                $sub->whereNotNull('fully_returned_at')
                                    ->orWhereRaw('returned_quantity >= quantity');
                            }),
                            default => $query,
                        };
                    }),

                SelectFilter::make('employee_id')
                    ->label('Peminjam (Pegawai)')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('item_id')
                    ->label('Barang / Alat')
                    ->relationship('item', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('borrowed_at')
                    ->form([
                        DatePicker::make('borrowed_from')->label('Dipinjam Dari'),
                        DatePicker::make('borrowed_until')->label('Dipinjam Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['borrowed_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('borrowed_at', '>=', $date))
                            ->when($data['borrowed_until'] ?? null, fn (Builder $q, $date) => $q->whereDate('borrowed_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('return_asset')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('success')
                    ->visible(function (AssetLoan $record): bool {
                        $isUnsettled = $record->fully_returned_at === null && $record->returned_quantity < $record->quantity;
                        $user = auth()->user();

                        return $isUnsettled && ($user?->hasAnyRole(['inventory_admin', 'super_admin']) ?? false);
                    })
                    ->form([
                        TextInput::make('item_display')
                            ->label('Alat Kerja')
                            ->default(fn (AssetLoan $record): string => "{$record->item->name} (SKU: {$record->item->sku})")
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('borrower_display')
                            ->label('Peminjam')
                            ->default(fn (AssetLoan $record): string => "{$record->employee->name} (NIK: {$record->employee->namecode})")
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('outstanding_display')
                            ->label('Sisa Pinjaman Belum Kembali')
                            ->default(fn (AssetLoan $record): string => max(0, $record->quantity - $record->returned_quantity) . ' ' . $record->item->unit)
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('return_quantity')
                            ->label('Jumlah Dikembalikan Saat Ini')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (AssetLoan $record): int => max(1, $record->quantity - $record->returned_quantity))
                            ->default(fn (AssetLoan $record): int => max(1, $record->quantity - $record->returned_quantity))
                            ->required()
                            ->helperText('Masukkan kuantitas fisik alat yang diserahkan kembali oleh auditor.'),

                        Textarea::make('notes')
                            ->label('Catatan Kondisi Alat & Pengembalian')
                            ->required()
                            ->rows(3)
                            ->placeholder('Contoh: Alat dikembalikan lengkap dan berfungsi normal.')
                            ->helperText('Wajib diisi untuk audit trail buku besar inventaris.'),
                    ])
                    ->modalHeading(fn (AssetLoan $record): string => "Pengembalian Alat — {$record->item->name}")
                    ->modalDescription('Pengembalian alat akan otomatis menambah stok fisik inventaris dan dicatat pada buku besar.')
                    ->modalSubmitActionLabel('Proses Pengembalian')
                    ->action(function (AssetLoan $record, array $data): void {
                        $user = auth()->user() ?? User::where('email', 'admin@admin.com')->first() ?? User::first();
                        if (! $user) {
                            return;
                        }

                        app(InventoryService::class)->returnItem(
                            identifier: (string) $record->item_id,
                            quantity: (int) $data['return_quantity'],
                            userId: (string) $user->id,
                            notes: (string) $data['notes'],
                            employeeId: (string) $record->employee_id
                        );

                        Notification::make()
                            ->title('Alat Berhasil Dikembalikan')
                            ->body("{$data['return_quantity']} {$record->item->unit} {$record->item->name} telah berhasil dikembalikan ke inventaris.")
                            ->success()
                            ->send();
                    }),

                Action::make('view_timeline')
                    ->label('Riwayat')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->slideOver()
                    ->modalHeading(fn (AssetLoan $record): string => "Riwayat Pengembalian — {$record->item->name}")
                    ->modalDescription(fn (AssetLoan $record): string => "Peminjam: {$record->employee->name} | Total Dipinjam: {$record->quantity} {$record->item->unit}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->infolist([
                        RepeatableEntry::make('returns')
                            ->label('Histori Pengembalian Bertahap')
                            ->schema([
                                TextEntry::make('returned_at')
                                    ->label('Waktu')
                                    ->dateTime('d M Y H:i'),
                                TextEntry::make('quantity')
                                    ->label('Jumlah')
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('ledger.user.name')
                                    ->label('Diterima Admin')
                                    ->default('-'),
                                TextEntry::make('ledger.notes')
                                    ->label('Catatan')
                                    ->default('-'),
                            ])
                            ->columns(4)
                            ->placeholder('Belum ada riwayat pengembalian untuk transaksi ini.'),
                    ]),
            ]);
    }
}
