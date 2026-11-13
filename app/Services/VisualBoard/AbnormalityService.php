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
     *
     * @param  array<string, mixed>  $data
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

        $recipients = collect();

        if ($abnormality->pic) {
            $recipients->push($abnormality->pic);
        }

        if ($abnormality->zone) {
            $zone = $abnormality->zone;
            $zone->loadMissing(['picUtama.user', 'picPengganti.user']);
            if ($zone->picUtama?->user) {
                $recipients->push($zone->picUtama->user);
            }
            if ($zone->picPengganti?->user) {
                $recipients->push($zone->picPengganti->user);
            }
        }

        $superAdmins = User::role('super_admin')->get();
        $recipients = $recipients->merge($superAdmins);

        $uniqueRecipients = $recipients->unique('id');

        if ($uniqueRecipients->isNotEmpty()) {
            Notification::make()
                ->title('Temuan 5R Baru')
                ->body("Terdapat temuan abnormality baru di area {$abnormality->zone?->name} yang perlu diperiksa.")
                ->actions([
                    Action::make('view')
                        ->label('Lihat Detail')
                        ->button()
                        ->url(AbnormalityResource::getUrl('edit', ['record' => $abnormality->id])),
                ])
                ->success()
                ->sendToDatabase($uniqueRecipients);
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
            $abnormality = $this->repository->findById($id);
            if (! $abnormality->pic_id) {
                $data['pic_id'] = auth()->id();
            }
        }

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
     *
     * @param  array<string, mixed>  $data
     */
    public function updateAbnormality(string $id, array $data): Abnormality
    {
        $abnormality = $this->repository->update($id, $data);

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
     *
     * @return array<string, mixed>
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

            if ($isStaffVerification) {
                $managers = collect();

                if ($record->pic && $record->pic->hasRole('managerial_staff')) {
                    $managers->push($record->pic);
                }

                $superAdmins = User::role('super_admin')->get();
                $managers = $managers->merge($superAdmins);

                $uniqueManagers = $managers->unique('id');

                if ($uniqueManagers->isNotEmpty()) {
                    Notification::make()
                        ->title('Temuan 5R Menunggu Verifikasi Manajer')
                        ->body("Staff telah memverifikasi temuan di {$record->zone?->name}. Menunggu verifikasi Anda.")
                        ->actions([
                            Action::make('view')
                                ->label('Lihat Detail')
                                ->button()
                                ->url(AbnormalityResource::getUrl('edit', ['record' => $record->id])),
                        ])
                        ->info()
                        ->sendToDatabase($uniqueManagers);
                }
            }

            return ['success' => true, 'message' => 'Temuan berhasil diverifikasi'];
        }

        throw new UnauthorizedVerificationException;
    }

    /**
     * Delete an abnormality (soft delete).
     */
    public function deleteAbnormality(string $id): void
    {
        $abnormality = $this->repository->findById($id);
        if ($abnormality) {
            $abnormality->delete();

            Log::info('Abnormality: deleted', [
                'abnormality_id' => $id,
                'deleted_by' => auth()->id() ?? 'system',
            ]);
        }
    }
}
