<?php

namespace App\Services\VisualBoard;

use App\Domains\Core\Models\User;
use App\Domains\VisualBoard\Exceptions\UnauthorizedVerificationException;
use App\Domains\VisualBoard\Models\Abnormality;
use App\Filament\Resources\Abnormalities\AbnormalityResource;
use App\Repositories\Contracts\AbnormalityRepositoryInterface;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class AbnormalityService
{
    protected AbnormalityRepositoryInterface $repository;

    public function __construct(AbnormalityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new abnormality.
     */
    public function createAbnormality(array $data): Abnormality
    {
        $data['status'] = 'open';
        $data['progress_percentage'] = 0;

        $abnormality = $this->repository->create($data);

        Log::info('Abnormality: created', [
            'abnormality_id' => $abnormality->id,
            'zone_id' => $data['zone_id'],
            'reported_by' => $data['reported_by_id'] ?? null,
            'status' => 'open',
        ]);

        // Notifikasi ke semua staff penyelia & super admin
        $recipients = User::role(['staff_penyelia', 'super_admin'])->get();
        if ($recipients->isNotEmpty()) {
            Notification::make()
                ->title('Temuan 5R Baru')
                ->body('Terdapat temuan abnormality baru yang perlu diperiksa.')
                ->actions([
                    Action::make('view')
                        ->label('Lihat Detail')
                        ->button()
                        ->url(AbnormalityResource::getUrl('edit', ['record' => $abnormality->id])),
                ])
                ->success()
                ->sendToDatabase($recipients);
        }

        return $abnormality;
    }

    /**
     * Update progress of an abnormality.
     */
    public function updateProgress(string $id, int $percentage, ?string $actual = null, ?string $picId = null): Abnormality
    {
        $data = [
            'progress_percentage' => $percentage,
        ];

        if ($actual !== null) {
            $data['countermeasure_actual'] = $actual;
        }

        if ($picId !== null) {
            $data['pic_id'] = $picId;
        } elseif (auth()->check()) {
            // Auto set PIC if it's currently authenticated user and we don't have explicit picId
            // Only do this if the abnormality doesn't have a PIC yet (handled later or we just overwrite)
            $abnormality = $this->repository->findById($id);
            if (! $abnormality->pic_id) {
                $data['pic_id'] = auth()->id();
            }
        }

        // Determine status based on percentage
        if ($percentage == 100) {
            $data['status'] = 'resolved';
        } elseif ($percentage > 0) {
            $data['status'] = 'in_progress';
        } else {
            $data['status'] = 'open';
        }

        $abnormality = $this->repository->update($id, $data);

        Log::info('Abnormality: progress updated', [
            'abnormality_id' => $id,
            'progress' => $percentage,
            'new_status' => $data['status'],
            'user_id' => auth()->id(),
        ]);

        // Jika status menjadi resolved, beritahu pelapor
        if ($data['status'] === 'resolved' && $abnormality->reported_by_id) {
            $reporter = User::find($abnormality->reported_by_id);
            if ($reporter) {
                Notification::make()
                    ->title('Temuan 5R Diselesaikan')
                    ->body('Temuan yang Anda laporkan telah selesai ditangani.')
                    ->actions([
                        Action::make('view')
                            ->label('Lihat Detail')
                            ->button()
                            ->url(AbnormalityResource::getUrl('edit', ['record' => $abnormality->id])),
                    ])
                    ->success()
                    ->sendToDatabase($reporter);
            }
        }

        return $abnormality;
    }

    /**
     * Update an abnormality and handle notifications.
     */
    public function updateAbnormality(string $id, array $data): Abnormality
    {
        $abnormality = $this->repository->update($id, $data);

        // Jika status menjadi resolved, beritahu pelapor
        if (isset($data['status']) && $data['status'] === 'resolved' && $abnormality->reported_by_id) {
            $reporter = User::find($abnormality->reported_by_id);
            if ($reporter) {
                Notification::make()
                    ->title('Temuan 5R Diselesaikan')
                    ->body('Temuan yang Anda laporkan telah selesai ditangani.')
                    ->actions([
                        Action::make('view')
                            ->label('Lihat Detail')
                            ->button()
                            ->url(AbnormalityResource::getUrl('edit', ['record' => $abnormality->id])),
                    ])
                    ->success()
                    ->sendToDatabase($reporter);
            }
        }

        return $abnormality;
    }

    /**
     * Verify an abnormality.
     */
    public function verifyAbnormality(string $id, User $user): array
    {
        $record = $this->repository->findById($id);
        $roles = $user->getRoleNames();

        $now = now();
        $updated = false;
        $isStaffVerification = false;

        if ($roles->contains('staff_penyelia') && ! $record->verified_by_staff_id) {
            $record->verified_by_staff_id = $user->id;
            $record->verified_at_staff = $now;
            $updated = true;
            $isStaffVerification = true;
        } elseif ($roles->contains('managerial_staff') && ! $record->verified_by_ms_id) {
            $record->verified_by_ms_id = $user->id;
            $record->verified_at_ms = $now;
            $updated = true;
        } elseif ($roles->contains('super_admin')) {
            if (! $record->verified_by_staff_id) {
                $record->verified_by_staff_id = $user->id;
                $record->verified_at_staff = $now;
                $updated = true;
                $isStaffVerification = true;
            } elseif (! $record->verified_by_ms_id) {
                $record->verified_by_ms_id = $user->id;
                $record->verified_at_ms = $now;
                $updated = true;
            }
        }

        if ($updated) {
            $record->save();

            Log::info('Abnormality: verified', [
                'abnormality_id' => $id,
                'verified_by' => $user->id,
                'level' => $isStaffVerification ? 'staff' : 'manager',
            ]);

            // Notifikasi ke Managerial Staff & Super Admin jika diverifikasi oleh Staff Penyelia (eskalasi)
            if ($isStaffVerification) {
                $managers = User::role(['managerial_staff', 'super_admin'])->get();
                if ($managers->isNotEmpty()) {
                    Notification::make()
                        ->title('Temuan 5R Menunggu Verifikasi Manajer')
                        ->body('Staff telah memverifikasi temuan. Menunggu verifikasi Anda.')
                        ->actions([
                            Action::make('view')
                                ->label('Lihat Detail')
                                ->button()
                                ->url(AbnormalityResource::getUrl('edit', ['record' => $record->id])),
                        ])
                        ->info()
                        ->sendToDatabase($managers);
                }
            }

            return ['success' => true, 'message' => 'Temuan berhasil diverifikasi'];
        }

        throw new UnauthorizedVerificationException;
    }
}
