<?php

namespace App\Filament\Resources\Items\Tables;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\Item;
use App\Exports\StockOpnameExport;
use App\Services\Inventory\InventoryService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('Gambar')
                    ->collection('item_images'),
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->searchable(),
                TextColumn::make('unit')
                    ->label('Satuan')
                    ->searchable(),
                TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->sortable()
                    ->color(fn (Item $record): string => $record->current_stock <= $record->minimum_stock ? 'danger' : 'primary')
                    ->weight(fn (Item $record): string => $record->current_stock <= $record->minimum_stock ? 'bold' : 'regular'),
                TextColumn::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('critical_stock')
                    ->label('Kondisi Stok')
                    ->placeholder('Semua Kondisi')
                    ->trueLabel('Stok Kritis (<= Stok Minimum)')
                    ->falseLabel('Stok Aman (> Stok Minimum)')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereColumn('current_stock', '<=', 'minimum_stock'),
                        false: fn (Builder $query): Builder => $query->whereColumn('current_stock', '>', 'minimum_stock'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                TrashedFilter::make(),
            ])
            ->headerActions([
                Action::make('export_stock_opname')
                    ->label('Ekspor Form Stock Opname')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (): BinaryFileResponse {
                        $auditorName = auth()->user()->name ?? 'Auditor Internal';
                        $filename = 'Berita_Acara_Stock_Opname_' . now()->format('Ymd_His') . '.xlsx';

                        /** @var BinaryFileResponse $response */
                        $response = Excel::download(new StockOpnameExport($auditorName), $filename);

                        return $response;
                    }),
            ])
            ->recordActions([
                Action::make('adjust_stock')
                    ->label('Koreksi Stok')
                    ->icon('heroicon-o-scale')
                    ->color('warning')
                    ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false)
                    ->form([
                        TextInput::make('current_stock_display')
                            ->label('Stok Tercatat Saat Ini')
                            ->default(fn (Item $record): int => (int) $record->current_stock)
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('counted_quantity')
                            ->label('Jumlah Fisik Sebenarnya (Hasil Opname)')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->helperText('Masukkan jumlah fisik riil barang saat stock opname.'),
                        Textarea::make('reason')
                            ->label('Alasan Koreksi (Audit Trail)')
                            ->required()
                            ->rows(3)
                            ->placeholder('Contoh: Stock Opname Fisik Q4 / Selisih barang rusak / Penyesuaian audit berkala')
                            ->helperText('Wajib diisi untuk transparansi catatan audit trail pada buku besar inventaris.'),
                    ])
                    ->modalHeading(fn (Item $record): string => "Koreksi Stok — {$record->name}")
                    ->modalDescription('Penyesuaian stok akan dicatat dalam Inventory Ledger dengan tipe "adjustment" lengkap dengan audit trail nama admin, tanggal, dan alasan.')
                    ->modalSubmitActionLabel('Simpan Koreksi Stok')
                    ->action(function (Item $record, array $data): void {
                        $userId = auth()->id() ?? User::where('email', 'admin@admin.com')->value('id') ?? User::first()?->id;

                        app(InventoryService::class)->adjustStock(
                            item: $record,
                            countedQuantity: (int) $data['counted_quantity'],
                            reason: (string) $data['reason'],
                            userId: (string) $userId,
                        );

                        Notification::make()
                            ->title('Stok Berhasil Dikoreksi')
                            ->body("Stok {$record->name} telah disesuaikan menjadi {$data['counted_quantity']} {$record->unit}.")
                            ->success()
                            ->send();
                    }),
                Action::make('print_label')
                    ->label('Cetak Label Aset')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (Item $record): string => route('filament.admin.print.items', ['ids' => $record->id]))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('print_selected_labels')
                        ->label('Cetak Label Terpilih')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function (Collection $records, Component $livewire): void {
                            $ids = $records->pluck('id')->join(',');
                            $url = route('filament.admin.print.items', ['ids' => $ids]);
                            $livewire->js("window.open('{$url}', '_blank')");
                        }),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
