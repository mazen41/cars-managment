<?php


namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Spatie\Health\Facades\Health;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\CpuLoadHealthCheck\CpuLoadCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\HorizonCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use App\Checks\ReverbCheck;
use App\Checks\SmsDeviceCheck;
use App\Checks\ServerRamUsageCheck;
use App\Checks\PhpRamUsageCheck;
use Spatie\Health\Checks\Checks\RedisMemoryUsageCheck;

class HealthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Health::checks([
            DebugModeCheck::new(),
           EnvironmentCheck::new(),
            UsedDiskSpaceCheck::new()
            ->warnWhenUsedSpaceIsAbovePercentage(60)
            ->failWhenUsedSpaceIsAbovePercentage(80),
            DatabaseCheck::new(),
            CacheCheck::new(),
            CpuLoadCheck::new()
            ->failWhenLoadIsHigherInTheLast5Minutes(3.0)
            ->failWhenLoadIsHigherInTheLast15Minutes(4.0),
            HorizonCheck::new(),
            RedisCheck::new(),
            ScheduleCheck::new(),
            RedisMemoryUsageCheck::new()
               ->failWhenAboveMb(500),
               //custom checks
               ServerRamUsageCheck::new(),
               PHPRamUsageCheck::new()->label('PHP RAM Usage'),
               ReverbCheck::new()->label('Reverb Server Status'),
            //   // SmsDeviceCheck::new()->label('SMS24GateWay Device Status')->everyFiveMinutes(),
        ]);
    }
}
