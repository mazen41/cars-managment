<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
        $schedule->command('app:clean-exports-directory')->daily();

        // Auction system scheduled commands
        $schedule->command('auction:start-scheduled-rooms')->everyMinute();
        $schedule->command('auction:send-timer-updates')->everyFiveSeconds()->onSuccessWithOutput(function ($output) {
            //\Log::info('Auction Timer Updates Sent', ['output' => $output]);
        })->onFailure(function () {
            //\Log::error('Auction Timer Updates Failed');
        });
        $schedule->command('auction:cleanup-expired-offers')->hourly();

        // Car view count synchronization - sync every 15 minutes
        $schedule->command('view:sync "App\Services\CarViewTrackingService"')->everyFifteenMinutes();

        // Customer Product view count synchronization - sync every 15 minutes
        $schedule->command('view:sync "App\Services\CustomerProductViewTrackingService"')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
