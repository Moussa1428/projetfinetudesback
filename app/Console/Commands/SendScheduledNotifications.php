<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use Carbon\Carbon;
use App\Http\Controllers\NotificationAPI\NotificationController;


class SendScheduledNotifications extends Command
{

    protected $signature = 'notifications:send-scheduled';
    protected $description = 'Envoie les notifications planifiées dont la date est arrivée.';

    public function handle()
    {
        $this->info("Now: " . now());

        $notifications = Notification::where('scheduled_at', '<=', now())
                             ->where('is_sent', false)
                             ->get();

        if ($notifications->isEmpty()) {
            $this->info("Aucune notification prête à être envoyée.");
            return;
        }

        foreach ($notifications as $notif) {
            $this->info("Notif #{$notif->id} - scheduled_at: {$notif->scheduled_at}");

            app(NotificationController::class)->dispatchNotification($notif);

            $notif->update(['is_sent' => true]);

            $this->info("Notification #{$notif->id} envoyée.");
        }
    }
}
