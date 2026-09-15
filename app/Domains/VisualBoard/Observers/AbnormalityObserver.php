<?php

namespace App\Domains\VisualBoard\Observers;

use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Notifications\NewAbnormalityNotification;
use Illuminate\Support\Facades\Notification;

class AbnormalityObserver
{
    /**
     * Handle the Abnormality "created" event.
     */
    public function created(Abnormality $abnormality): void
    {
        $zone = $abnormality->zone;

        if (! $zone) {
            return;
        }

        $users = collect([
            $zone->picUtama?->user,
            $zone->picPengganti?->user,
        ])->filter();

        if ($users->isNotEmpty()) {
            Notification::send($users, new NewAbnormalityNotification($abnormality));
        }
    }
}
