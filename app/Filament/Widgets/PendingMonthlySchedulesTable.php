<?php

namespace App\Filament\Widgets;

use App\Domains\VisualBoard\Models\MonthlySchedule;
use App\Services\VisualBoard\MonthlyScheduleService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Notifications\Notification;

class PendingMonthlySchedulesTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Jadwal Bulanan (Menunggu Approval)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MonthlySchedule::query()->where('status', 'draft')
            )
            ->columns([
                Tables\Columns\TextColumn::make('zone.name')
                    ->label('Zona')
                    ->searchable(),
                Tables\Columns\TextColumn::make('period_month')
                    ->label('Periode')
                    ->date('F Y'),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh'),
                Tables\Columns\TextColumn::make('approval_status')
                    ->label('Status Approval')
                    ->getStateUsing(function (MonthlySchedule $record) {
                        $data = $record->approval_data ?? [];
                        if (isset($data['vp_signed'])) return 'Approved by VP';
                        if (isset($data['manager_signed'])) return 'Approved by Manager';
                        if (isset($data['staff_signed'])) return 'Approved by Staff';
                        if (isset($data['pic_signed'])) return 'Approved by PIC';
                        return 'Menunggu PIC';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved by VP' => 'success',
                        'Approved by Manager' => 'success',
                        'Approved by Staff' => 'info',
                        'Approved by PIC' => 'info',
                        default => 'warning',
                    }),
            ])
            ->actions([
                \Filament\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (MonthlySchedule $record) {
                        $user = auth()->user();
                        $result = app(MonthlyScheduleService::class)
                            ->approveSchedule($record->id, $user);

                        if ($result['success']) {
                            Notification::make()
                                ->title($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title($result['message'])
                                ->danger()
                                ->send();
                        }
                    })
            ]);
    }
}

