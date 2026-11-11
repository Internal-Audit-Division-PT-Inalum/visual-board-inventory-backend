<?php

namespace App\Services\VisualBoard;

use App\Domains\Core\Models\User;
use App\Filament\Resources\MonthlySchedules\MonthlyScheduleResource;
use App\Repositories\Contracts\MonthlyScheduleRepositoryInterface;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MonthlyScheduleService
{
    public function __construct(
        private MonthlyScheduleRepositoryInterface $repository
    ) {}

    /**
     * Proses approval bertingkat pada jadwal bulanan.
     * Urutan: PIC → Staff → Manager → VP (sesuai GEMINI.md §5A approval_data)
     *
     * @return array{success: bool, message: string, level: string|null}
     */
    public function approveSchedule(string $id, User $user): array
    {
        $record = $this->repository->findById($id);

        if (! $record) {
            Log::warning('MonthlySchedule: approval attempted on non-existent record', [
                'schedule_id' => $id,
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'message' => 'Jadwal bulanan tidak ditemukan.',
                'level' => null,
            ];
        }

        $roles = $user->getRoleNames();
        $data = $record->approval_data ?? [];
        $now = Carbon::now()->toIso8601String();
        $approvedLevel = null;

        if ($roles->contains('pelaksana_5r') && ! isset($data['pic_signed'])) {
            $data['pic_signed'] = ['user_id' => $user->id, 'signed_at' => $now];
            $approvedLevel = 'pic';
        } elseif ($roles->contains('staff_penyelia') && ! isset($data['staff_signed'])) {
            $data['staff_signed'] = ['user_id' => $user->id, 'signed_at' => $now];
            $approvedLevel = 'staff';
        } elseif ($roles->contains('managerial_staff') && ! isset($data['manager_signed'])) {
            $data['manager_signed'] = ['user_id' => $user->id, 'signed_at' => $now];
            $approvedLevel = 'manager';
        } elseif ($roles->contains('super_admin')) {
            // Super admin mengisi level approval berikutnya yang kosong
            if (! isset($data['pic_signed'])) {
                $data['pic_signed'] = ['user_id' => $user->id, 'signed_at' => $now];
                $approvedLevel = 'pic';
            } elseif (! isset($data['staff_signed'])) {
                $data['staff_signed'] = ['user_id' => $user->id, 'signed_at' => $now];
                $approvedLevel = 'staff';
            } elseif (! isset($data['manager_signed'])) {
                $data['manager_signed'] = ['user_id' => $user->id, 'signed_at' => $now];
                $approvedLevel = 'manager';
            } elseif (! isset($data['vp_signed'])) {
                $data['vp_signed'] = ['user_id' => $user->id, 'signed_at' => $now];
                $approvedLevel = 'vp';
            }
        }

        if ($approvedLevel === null) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk meng-approve jadwal ini, atau approval sudah dilakukan.',
                'level' => null,
            ];
        }

        $record->approval_data = $data;

        // Transisi status otomatis: jika Manager atau VP sudah approve, jadwal aktif
        if (isset($data['manager_signed']) || isset($data['vp_signed'])) {
            $record->status = 'in_progress';
        }

        $record->save();

        // Structured logging (GEMINI.md §8)
        Log::info('MonthlySchedule: approved', [
            'schedule_id' => $record->id,
            'user_id' => $user->id,
            'level' => $approvedLevel,
            'zone_id' => $record->zone_id,
        ]);

        // Notifikasi ke penanggung jawab spesifik jadwal/zona (bukan broadcast kolektif seluruh role)
        if ($approvedLevel === 'manager' || $approvedLevel === 'vp') {
            $recipients = collect();

            if ($record->creator) {
                $recipients->push($record->creator);
            }

            $schedulePicUsers = User::whereIn('id', $record->schedulePics()->pluck('pic_id'))->get();
            $recipients = $recipients->merge($schedulePicUsers);

            if ($record->zone) {
                $zone = $record->zone;
                $zone->loadMissing(['picUtama.user', 'picPengganti.user']);
                if ($zone->picUtama?->user) {
                    $recipients->push($zone->picUtama->user);
                }
                if ($zone->picPengganti?->user) {
                    $recipients->push($zone->picPengganti->user);
                }
            }

            $uniqueRecipients = $recipients->unique('id');

            if ($uniqueRecipients->isNotEmpty()) {
                Notification::make()
                    ->title('Jadwal Bulanan Telah Di-Approve')
                    ->body("Jadwal untuk zona {$record->zone?->name} sudah di-approve dan siap dieksekusi.")
                    ->actions([
                        Action::make('view')
                            ->label('Lihat Jadwal')
                            ->button()
                            ->url(MonthlyScheduleResource::getUrl('edit', ['record' => $record->id])),
                    ])
                    ->success()
                    ->sendToDatabase($uniqueRecipients);
            }
        }

        return [
            'success' => true,
            'message' => 'Jadwal berhasil di-approve pada level: ' . strtoupper($approvedLevel),
            'level' => $approvedLevel,
        ];
    }
}
