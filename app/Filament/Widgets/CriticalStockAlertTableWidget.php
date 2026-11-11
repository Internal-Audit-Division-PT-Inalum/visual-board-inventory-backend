<?php

namespace App\Filament\Widgets;

use App\Domains\Core\Models\User;
use App\Domains\Inventory\Models\Item;
use App\Filament\Resources\Items\ItemResource;
use App\Services\Inventory\InventoryService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CriticalStockAlertTableWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Peringatan Stok Kritis (Mendekati / Di Bawah Batas Minimum)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Item::query()
                    ->where('is_active', true)
                    ->whereColumn('current_stock', '<=', 'minimum_stock')
                    ->with(['location'])
                    ->orderBy('current_stock', 'asc')
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('Gambar')
                    ->collection('item_images')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nama Barang')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('location.name')
                    ->label('Lokasi Rak')
                    ->icon('heroicon-m-map-pin')
                    ->placeholder('-'),

                TextColumn::make('stock_status')
                    ->label('Kondisi Persediaan')
                    ->getStateUsing(fn (Item $record): string => "{$record->current_stock} / {$record->minimum_stock} {$record->unit}")
                    ->badge()
                    ->color('danger'),

                TextColumn::make('current_stock')
                    ->label('Sisa Stok')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('danger'),

                TextColumn::make('minimum_stock')
                    ->label('Batas Minimum')
                    ->numeric(),

                TextColumn::make('unit')
                    ->label('Satuan'),
            ])
            ->headerActions([
                Action::make('view_all_critical')
                    ->label('Lihat Semua Stok Kritis')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->button()
                    ->url(ItemResource::getUrl('index', ['tableFilters[critical_stock][value]' => '1'])),
            ])
            ->recordActions([
                Action::make('adjust_stock')
                    ->label('Koreksi Stok')
                    ->icon('heroicon-o-scale')
                    ->color('warning')
                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['inventory_admin', 'super_admin']) ?? false)
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
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}

