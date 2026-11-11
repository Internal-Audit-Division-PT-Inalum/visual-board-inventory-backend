<?php

namespace App\Filament\Resources\Items\Pages;

use App\Exports\StockOpnameExport;
use App\Filament\Resources\Items\ItemResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'asset' => Tab::make('Aset')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'asset')),
            'consumable' => Tab::make('Consumable')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'consumable')),
        ];
    }
}
