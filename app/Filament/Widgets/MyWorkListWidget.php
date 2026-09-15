<?php

namespace App\Filament\Widgets;

use App\Domains\VisualBoard\Models\Abnormality;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyWorkListWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Pekerjaan Saya (My Work)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Abnormality::query()
                    ->where('status', '!=', 'resolved')
                    ->where(function (Builder $query) {
                        $query->where('pic_id', Auth::id())
                            ->orWhere('reported_by_id', Auth::id());
                    })
                    ->latest('updated_at')
            )
            ->columns([
                Split::make([
                    Stack::make([
                        Tables\Columns\TextColumn::make('problem_description')
                            ->weight('bold')
                            ->size('lg')
                            ->searchable(),
                        Tables\Columns\TextColumn::make('zone.name')
                            ->icon('heroicon-m-map-pin')
                            ->color('gray'),
                    ])->space(1),

                    Stack::make([
                        Tables\Columns\TextColumn::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'open' => 'danger',
                                'in_progress' => 'warning',
                                'resolved' => 'success',
                                default => 'gray',
                            }),
                        Tables\Columns\TextColumn::make('progress_percentage')
                            ->formatStateUsing(fn ($state) => "Progress: {$state}%")
                            ->color('gray'),
                    ])->space(1)->alignment('right'),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->actions([
                Action::make('kerjakan')
                    ->label('Buka Pekerjaan')
                    ->button()
                    ->url(fn (Abnormality $record): string => route('filament.admin.resources.abnormalities.edit', ['record' => $record])),
            ])
            ->paginated([4, 8, 12])
            ->defaultPaginationPageOption(4);
    }
}
