<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Executa diariamente às 00:01. O próprio comando aplicará a periodicidade configurada.
        $schedule->command('cashback:calculate')->dailyAt('00:01');
        
        // Sistema de distribuição - executa a cada 3 minutos
        $schedule->command('distribution:process')->everyThreeMinutes();
        $schedule->command('distribution:process-30s')->everyThreeMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
