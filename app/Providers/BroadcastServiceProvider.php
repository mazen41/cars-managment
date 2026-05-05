<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    // For Web Dashboard (session auth)
    Broadcast::routes([
        'middleware' => ['web', 'auth'],
    ]);

    // For API (Sanctum token auth)
    Broadcast::routes([
        'middleware' => ['api', 'auth:sanctum'],
        'prefix' => 'api/v2'
    ]);

    require base_path('routes/channels.php');
  }
}
