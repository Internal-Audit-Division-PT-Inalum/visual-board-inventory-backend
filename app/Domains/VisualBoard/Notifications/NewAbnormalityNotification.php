<?php

namespace App\Domains\VisualBoard\Notifications;

use App\Domains\VisualBoard\Models\Abnormality;
use App\Filament\Resources\Abnormalities\AbnormalityResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAbnormalityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Abnormality $abnormality;

    public function __construct(Abnormality $abnormality)
    {
        $this->abnormality = $abnormality;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $zoneName = $this->abnormality->zone->name ?? 'Unknown Zone';
        $foundDate = $this->abnormality->date_found ? $this->abnormality->date_found->format('d M Y') : 'Unknown Date';

        $url = AbnormalityResource::getUrl('edit', ['record' => $this->abnormality]);

        return (new MailMessage)
            ->subject('Laporan Temuan (Abnormality) Baru: ' . $zoneName)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Sistem telah mencatat adanya temuan (abnormality) baru di area tanggung jawab Anda.')
            ->line('**Zona:** ' . $zoneName)
            ->line('**Tanggal Ditemukan:** ' . $foundDate)
            ->line('**Deskripsi Temuan:** ' . ($this->abnormality->description ?? '-'))
            ->action('Lihat Detail Temuan', $url)
            ->line('Mohon segera ditindaklanjuti untuk mempertahankan standar 5R.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'abnormality_id' => $this->abnormality->id,
            'zone_id' => $this->abnormality->zone_id,
            'description' => $this->abnormality->description,
        ];
    }
}
