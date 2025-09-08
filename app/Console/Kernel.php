<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Définir les commandes Artisan personnalisées
     *
     * @var array
     */
    protected $commands = [
        // tu peux lister tes commandes ici si besoin
        // \App\Console\Commands\SendScheduledNotifications::class,
    ];

    /**
     * Définir la planification des tâches
     */
    protected function schedule(Schedule $schedule): void
    {
        // Exemple : exécuter la commande de notification planifiée chaque minute
        $schedule->command('notifications:send-scheduled')->everyMinute();
    }

    /**
     * Enregistrer les commandes Artisan de l'application
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
