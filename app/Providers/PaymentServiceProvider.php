<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Payment\PaymentService;
use App\Services\Payment\PaymentFactory;
use App\Contracts\Payment\PaymentProviderInterface;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register PaymentFactory as singleton
        $this->app->singleton(PaymentFactory::class, function ($app) {
            return new PaymentFactory();
        });

        // Register PaymentService as singleton
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService($app->make(PaymentFactory::class));
        });

        // Bind the payment service interface if needed
        $this->app->bind('payment.service', PaymentService::class);
        $this->app->bind('payment.factory', PaymentFactory::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // You can add additional providers here at runtime if needed
        $this->registerAdditionalProviders();
    }

    /**
     * Register additional payment providers from configuration
     */
    private function registerAdditionalProviders(): void
    {
        $factory = $this->app->make(PaymentFactory::class);

        // Get additional providers from config if any
        $additionalProviders = config('payment.additional_providers', []);

        foreach ($additionalProviders as $name => $config) {
            if (isset($config['class']) && class_exists($config['class'])) {
                $factory->registerProvider(
                    $name,
                    $config['class'],
                    $config['config'] ?? []
                );
            }
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            PaymentService::class,
            PaymentFactory::class,
            'payment.service',
            'payment.factory',
        ];
    }
}
