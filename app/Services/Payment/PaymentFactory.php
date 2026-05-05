<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentProviderInterface;
use App\Services\Payment\Providers\FloosakProvider;
use App\Services\Payment\Providers\JaibProvider;
use App\Services\Payment\Providers\JawaliProvider;
use Exception;

class PaymentFactory
{
    private array $providers = [];
    private array $providerConfigs = [];

    public function __construct()
    {
        $this->initializeProviders();
    }

    /**
     * Create a payment provider instance
     */
    public function createProvider(
        string $providerName,
    ): PaymentProviderInterface {
        $providerName = strtolower($providerName);

        if (!isset($this->providers[$providerName])) {
            throw new Exception(
                "Payment provider '{$providerName}' is not supported",
            );
        }

        $providerClass = $this->providers[$providerName];
        $config = $this->providerConfigs[$providerName] ?? [];

        // Check if the provider class exists
        if (!class_exists($providerClass)) {
            throw new Exception(
                "Payment provider class '{$providerClass}' not found",
            );
        }

        // Create instance
        $provider = new $providerClass($config);

        // Ensure it implements the interface
        if (!$provider instanceof PaymentProviderInterface) {
            throw new Exception(
                "Payment provider '{$providerName}' must implement PaymentProviderInterface",
            );
        }

        return $provider;
    }

    /**
     * Get all supported providers
     */
    public function getSupportedProviders(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Check if provider is supported
     */
    public function isProviderSupported(string $providerName): bool
    {
        return isset($this->providers[strtolower($providerName)]);
    }

    /**
     * Register a new payment provider
     */
    public function registerProvider(
        string $name,
        string $className,
        array $config = [],
    ): void {
        $this->providers[strtolower($name)] = $className;
        $this->providerConfigs[strtolower($name)] = $config;
    }

    /**
     * Get provider configuration
     */
    public function getProviderConfig(string $providerName): array
    {
        return $this->providerConfigs[strtolower($providerName)] ?? [];
    }

    /**
     * Update provider configuration
     */
    public function updateProviderConfig(
        string $providerName,
        array $config,
    ): void {
        $this->providerConfigs[strtolower($providerName)] = array_merge(
            $this->providerConfigs[strtolower($providerName)] ?? [],
            $config,
        );
    }

    /**
     * Get providers that support a specific currency
     */
    public function getProvidersForCurrency(string $currencyCode): array
    {
        $supportingProviders = [];

        foreach ($this->providers as $name => $class) {
            try {
                $provider = $this->createProvider($name);
                if ($provider->supportsCurrency($currencyCode)) {
                    $supportingProviders[] = $name;
                }
            } catch (Exception $e) {
                // Skip providers that can't be instantiated
                continue;
            }
        }

        return $supportingProviders;
    }

    /**
     * Get provider capabilities
     */
    public function getProviderCapabilities(string $providerName): array
    {
        try {
            $provider = $this->createProvider($providerName);

            return [
                "name" => $provider->getProviderName(),
                "supported_currencies" => $provider->getSupportedCurrencies(),
                "supports_refund" => true, // All providers should support refund
                "supports_status_check" => true, // All providers should support status check
                "supports_code_validation" => true, // All providers should support code validation
            ];
        } catch (Exception $e) {
            return [
                "name" => $providerName,
                "error" => $e->getMessage(),
                "supported_currencies" => [],
                "supports_refund" => false,
                "supports_status_check" => false,
                "supports_code_validation" => false,
            ];
        }
    }

    /**
     * Initialize default providers
     */
    private function initializeProviders(): void
    {
        //Wallet Provider
        $this->providers["wallet"] = \App\Services\Payment\Providers\WalletProvider::class;
        $this->providerConfigs["wallet"] = [];

        // Jaib Provider
        $this->providers["jaib"] = JaibProvider::class;
        $this->providerConfigs["jaib"] = [
            "user" => env("JAIB_USER"),
            "pass" => env("JAIB_PASS"),
            "agent_code" => env("JAIB_AGENT_CODE"),
        ];

        // Jawali Provider
        $this->providers["jawali"] = JawaliProvider::class;
        $this->providerConfigs["jawali"] = [
            "username" => env("JAWALI_USERNAME"),
            "password" => env("JAWALI_PASS"),
            "orgid" => env("JAWALI_ORGID"),
            "agent_id" => env("JAWALI_AGENT_ID"),
            "agent_pwd" => env("JAWALI_AGENT_PWD"),
        ];

        // Floosak Provider
        $this->providers["floosak"] = FloosakProvider::class;
        $this->providerConfigs["floosak"] = [
            "short_code" => env("FLOOSAK_SHORT_CODE"),
            "phone_number" => env("FLOOSAK_PHONE_NUMBER"),
            "api_key" => env("FLOOSAK_API_KEY"),
            "source_wallet_id" => env("FLOOSAK_SOURCE_WALLET_ID"),
        ];
    }

    /**
     * Create provider with fallback support
     */
    public function createProviderWithFallback(
        string $primaryProvider,
        array $fallbackProviders = [],
    ): PaymentProviderInterface {
        try {
            return $this->createProvider($primaryProvider);
        } catch (Exception $e) {
            foreach ($fallbackProviders as $fallbackProvider) {
                try {
                    return $this->createProvider($fallbackProvider);
                } catch (Exception $fallbackException) {
                    continue;
                }
            }

            throw new Exception(
                "Failed to create primary provider '{$primaryProvider}' and all fallback providers: " .
                    $e->getMessage(),
            );
        }
    }

    /**
     * Get provider statistics
     */
    public function getProviderStatistics(): array
    {
        $stats = [];

        foreach ($this->providers as $name => $class) {
            $stats[$name] = [
                "class" => $class,
                "available" => false,
                "error" => null,
                "supported_currencies" => [],
            ];

            try {
                $provider = $this->createProvider($name);
                $stats[$name]["available"] = true;
                $stats[$name][
                    "supported_currencies"
                ] = $provider->getSupportedCurrencies();
            } catch (Exception $e) {
                $stats[$name]["error"] = $e->getMessage();
            }
        }

        return $stats;
    }

    /**
     * Validate provider configuration
     */
    public function validateProviderConfig(string $providerName): array
    {
        $errors = [];

        try {
            $provider = $this->createProvider($providerName);

            // Try to authenticate to validate configuration
            $provider->authenticate();

            return $errors; // No errors if authentication succeeds
        } catch (Exception $e) {
            $errors[] = "Configuration validation failed: " . $e->getMessage();
        }

        return $errors;
    }
}
