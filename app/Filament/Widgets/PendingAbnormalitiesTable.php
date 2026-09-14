<?php

namespace App\Filament\Widgets;

use App\Domains\VisualBoard\Models\Abnormality;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class PendingAbnormalitiesTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Temuan 5R (Menunggu Verifikasi)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Abnormality::query()
                    ->whereNull('verified_by_staff_id')
                    ->orWhereNull('verified_by_ms_id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('zone.name')
                    ->label('Zona')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_found')
                    ->label('Tgl Ditemukan')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('problem_description')
                    ->label('Deskripsi')
                    ->limit(50),
                Tables\Columns\TextColumn::make('reportedBy.name')
                    ->label('Ditemukan Oleh'),
                Tables\Columns\TextColumn::make('verification_status')
                    ->label('Status Verifikasi')
                    ->getStateUsing(function (Abnormality $record) {
                        if ($record->verified_by_ms_id) return 'Verified by MS';
                        if ($record->verified_by_staff_id) return 'Verified by Staff';
                        return 'Menunggu Verifikasi';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Verified by MS' => 'success',
                        'Verified by Staff' => 'info',
                        default => 'warning',
                    }),
            ])
            ->actions([
                \Filament\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Abnormality $record) {
                        $user = auth()->user();
                        try {
                            $result = app(\App\Services\VisualBoard\AbnormalityService::class)->verifyAbnormality($record->id, $user);
                            Notification::make()
                                ->title($result['message'])
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
            ]);
    }
}
