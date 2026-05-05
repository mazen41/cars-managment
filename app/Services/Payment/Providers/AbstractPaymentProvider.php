<?php

namespace App\Services\Payment\Providers;

use App\Contracts\Payment\PaymentProviderInterface;
use App\Contracts\Payment\PaymentResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

abstract class AbstractPaymentProvider implements PaymentProviderInterface
{
    protected string $providerName;
    protected array $config;
    protected array $supportedCurrencies;
    protected ?string $accessToken = null;
    protected ?int $tokenExpiryTime = null;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->providerName = $this->getProviderName();
        $this->supportedCurrencies = $this->getSupportedCurrencies();
    }

    /**
     * Get provider name - must be implemented by concrete classes
     */
    abstract public function getProviderName(): string;

    /**
     * Get supported currencies - must be implemented by concrete classes
     */
    abstract public function getSupportedCurrencies(): array;

    /**
     * Authenticate with the provider - must be implemented by concrete classes
     */
    abstract public function authenticate(): void;

    /**
     * Execute HTTP request using cURL
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param mixed $data
     * @return array<string,mixed>
     */
    protected function makeHttpRequest(
        string $method,
        string $url,
        array $headers = [],
        $data = null,
    ): array {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($method === "POST" && $data !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            $this->logError("cURL request failed: {$error}");
            throw new \Exception(
                "Failed to connect to {$this->providerName} API: {$error}",
            );
        }

        $this->logInfo("HTTP {$method} to {$url}", [
            "http_code" => $httpCode,
            "response_length" => strlen($response),
        ]);

        return [
            "body" => $response,
            "http_code" => $httpCode,
            "success" => $httpCode >= 200 && $httpCode < 300,
        ];
    }

    /**
     * Parse JSON response
     */
    protected function parseJsonResponse(string $response, $associative = true): array | object
    {
        $decoded = json_decode($response, $associative);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error =
                "Invalid JSON response from {$this->providerName} API: " .
                json_last_error_msg();
            $this->logError($error, ["response" => $response]);
            throw new \Exception($error);
        }

        return $decoded;
    }

    /**
     * Cache access token
     * @param string $token
     * @param int $expirySeconds
     * @param array $additionalData
     */
    protected function cacheToken(
        string $token,
        int $expirySeconds,
        array $additionalData = [],
    ): void {
        $cacheKey = $this->getTokenCacheKey();
        $expiryCacheKey = $this->getTokenExpiryCacheKey();

        Cache::put($cacheKey, $token, $expirySeconds);
        Cache::put($expiryCacheKey, time() + $expirySeconds, $expirySeconds);

        foreach ($additionalData as $key => $value) {
            Cache::put("{$cacheKey}_{$key}", $value, $expirySeconds);
        }

        $this->accessToken = $token;
        $this->tokenExpiryTime = time() + $expirySeconds;

        $this->logInfo("Token cached successfully", [
            "expires_in" => $expirySeconds,
            "additional_data_keys" => array_keys($additionalData),
        ]);
    }

    /**
     * Get cached access token
     */
    protected function getCachedToken(): ?array
    {
        $cacheKey = $this->getTokenCacheKey();
        $expiryCacheKey = $this->getTokenExpiryCacheKey();

        if (!Cache::has($cacheKey) || !Cache::has($expiryCacheKey)) {
            return null;
        }

        $expiryTime = Cache::get($expiryCacheKey);
        if (time() >= $expiryTime) {
            $this->clearTokenCache();
            return null;
        }

        return [
            "access_token" => Cache::get($cacheKey),
            "expiry_time" => $expiryTime,
        ];
    }

    /**
     * Clear token cache
     */
    protected function clearTokenCache(): void
    {
        $cacheKey = $this->getTokenCacheKey();
        $expiryCacheKey = $this->getTokenExpiryCacheKey();

        Cache::forget($cacheKey);
        Cache::forget($expiryCacheKey);

        $this->accessToken = null;
        $this->tokenExpiryTime = null;
    }

    /**
     * Check if token is valid
     */
    protected function isTokenValid(): bool
    {
        if (
            $this->accessToken &&
            $this->tokenExpiryTime &&
            time() < $this->tokenExpiryTime
        ) {
            return true;
        }

        $cachedToken = $this->getCachedToken();
        if ($cachedToken) {
            $this->accessToken = $cachedToken["access_token"];
            $this->tokenExpiryTime = $cachedToken["expiry_time"];
            return true;
        }

        return false;
    }

    /**
     * Get token cache key
     */
    protected function getTokenCacheKey(): string
    {
        return strtolower($this->providerName) . "_access_token";
    }

    /**
     * Get token expiry cache key
     */
    protected function getTokenExpiryCacheKey(): string
    {
        return strtolower($this->providerName) . "_token_expiry";
    }

    /**
     * Generate request ID
     */
    protected function generateRequestId(string $suffix = ""): string
    {
        return date("Ymd-His") . rand(10, 99) . $suffix;
    }

    /**
     * Normalize phone number
     */
    protected function normalizePhoneNumber(
        string $phone,
        string $countryCode = "+967",
    ): string {
        return str_replace($countryCode, "", $phone);
    }

    /**
     * Log info message
     * @param string $message
     * @param array $context
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info("[{$this->providerName}] {$message}", $context);
    }

    /**
     * Log error message
     * @param string $message
     * @param array $context
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[{$this->providerName}] {$message}", $context);
    }

    /**
     * Log debug message
     * @param string $message
     * @param array $context
     */
    protected function logDebug(string $message, array $context = []): void
    {
        Log::debug("[{$this->providerName}] {$message}", $context);
    }

    /**
     * Get configuration value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ??
            env(
                strtoupper($this->providerName) . "_" . strtoupper($key),
                $default,
            );
    }

    /**
     * Validate required configuration
     * @param array $requiredKeys
     */
    protected function validateConfig(array $requiredKeys): void
    {
        $missing = [];

        foreach ($requiredKeys as $key) {
            if (empty($this->getConfig($key))) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new \Exception(
                "Missing required {$this->providerName} configuration: " .
                    implode(", ", $missing),
            );
        }
    }

    /**
     * Check if provider supports currency
     */
    public function supportsCurrency(string $currencyCode): bool
    {
        return in_array(
            strtoupper($currencyCode),
            array_map("strtoupper", $this->supportedCurrencies),
        );
    }

    /**
     * Format amount for API
     */
    protected function formatAmount(float $amount): float
    {
        return round($amount, 2);
    }

    /**
     * Ensure authentication before API calls
     */
    protected function ensureAuthenticated(): void
    {
        if (!$this->isTokenValid()) {
            $this->authenticate();
        }
    }

    /**
     * Create standardized error response
     * @param string $message
     * @param array $rawResponse
     */
    protected function createErrorResponse(
        string $message,
        array $rawResponse = [],
    ): PaymentResponse {
        $this->logError("Payment failed: {$message}", [
            "raw_response" => $rawResponse,
        ]);
        return PaymentResponse::failure($message, $rawResponse);
    }

    /**
     * Create standardized success response
     * @param string $message
     * @param string|null $transactionId
     * @param string|null $referenceId
     * @param float|null $amount
     * @param string|null $currency
     * @param string|null $status
     * @param array $rawResponse
     * @param array $metadata
     */
    protected function createSuccessResponse(
        string $message,
        ?string $transactionId = null,
        ?string $referenceId = null,
        ?float $amount = null,
        ?string $currency = null,
        ?string $status = null,
        array $rawResponse = [],
        array $metadata = [],
    ): PaymentResponse {
        $this->logInfo("Payment successful: {$message}", [
            "transaction_id" => $transactionId,
            "reference_id" => $referenceId,
            "amount" => $amount,
            "currency" => $currency,
            "status" => $status,
        ]);

        return PaymentResponse::success(
            $message,
            $transactionId,
            $referenceId,
            $amount,
            $currency,
            $status,
            $rawResponse,
            $metadata,
        );
    }
}
