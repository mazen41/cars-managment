<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = null;

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();

        $this->configureRateLimiting();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapApiSellerRoutes();

        $this->mapAdminRoutes();

        $this->mapSellerRoutes();

        $this->mapRefundRoutes();

        $this->mapOtpRoutes();

        $this->mapOfflinePaymentRoutes();

        $this->mapDeliveryBoyRoutes();

        //$this->mapAuctionRoutes();

        //$this->mapTestRoutes();

        $this->mapWebRoutes();
        $this->mapCarsRoutes();
        $this->mapApiCarsRoutes();
        $this->mapApiAuctionRoutes();
        $this->mapApiDeliveryBoyRoutes();
        $this->mapApiInspectorRoutes();
    }

    /**
     * Define the "b2b" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWholesaleRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/wholesale.php'));
    }

    /**
     * Define the "delivery boy" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapDeliveryBoyRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/delivery_boy.php'));
    }

    /**
     * Define the "auction" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapAuctionRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/auction.php'));
    }

    /**
     * Define the "offline payment" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapOfflinePaymentRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/offline_payment.php'));
    }


    /**
     * Define the "refund" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapRefundRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/refund_request.php'));
    }


    /**
     * Define the "OTP System" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapOtpRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/otp.php'));
    }

    /**
     * Define the "updating" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapUpdateRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/update.php'));
    }

    /**
     * Define the "installation" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapInstallRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/install.php'));
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "admin" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapAdminRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/admin.php'));
    }

    /**
     * Define the "seller" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapSellerRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/seller.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiSellerRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api_seller.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }


    protected function mapTestRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/tests.php'));
    }


    protected function mapCarsRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/cars.php'));
    }

    protected function mapApiCarsRoutes()
    {
        Route::middleware('api')
            ->namespace($this->namespace)
            ->prefix('api')
            ->group(base_path('routes/api_cars.php'));
    }
    protected function mapApiAuctionRoutes()
    {
        Route::middleware('api')
            ->prefix('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api_auction.php'));
    }

    protected function mapApiDeliveryBoyRoutes()
    {
        Route::middleware('api')
            ->namespace($this->namespace)
            ->prefix('api')
            ->group(base_path('routes/api_delivery_boy.php'));
    }

    protected function mapApiInspectorRoutes()
    {
        Route::middleware('api')
            ->namespace($this->namespace)
            ->prefix('api')
            ->group(base_path('routes/api_inspector.php'));
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // General API rate limiting: 60 requests per minute per user
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many requests. Please slow down.',
                        'error' => 'rate_limit_exceeded'
                    ], 429);
                });
        });

        // Auction bid rate limiting: 10 bids per minute per user
        RateLimiter::for('auction-bids', function (Request $request) {
            return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many bid attempts. Please wait before trying again.',
                        'error' => 'rate_limit_exceeded'
                    ], 429);
                });
        });

        // Auction offer rate limiting: 5 offers per minute per user
        RateLimiter::for('auction-offers', function (Request $request) {
            return Limit::perMinute(5)->by(optional($request->user())->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many offer attempts. Please wait before trying again.',
                        'error' => 'rate_limit_exceeded'
                    ], 429);
                });
        });
    }
}
