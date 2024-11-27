<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\SendClickAlerts::class,
        \App\Console\Commands\UpdatePendingInvoices::class, // Register the new command
    ];

    protected function schedule(Schedule $schedule)
    {

        $schedule->command('clicks:weekly-alerts')->weekly()->mondays()->at('09:00')->onOneServer();

        $schedule->command('clicks:send-alerts')->daily()->onOneServer();

        $schedule->command('invoices:updatePendingInvoices')->dailyAt('11:00');

        $schedule->command('data:send-reminder')->daily()->onOneServer();

        $schedule->command('plans:send-reminder')->daily()->onOneServer();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
