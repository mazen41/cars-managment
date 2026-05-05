<?php

namespace App\Services\Payment\Providers;

use App\Services\Payment\Providers\AbstractPaymentProvider;
use App\Contracts\Payment\PaymentResponse;
use Exception;
use App\Models\ApiKeys;
use App\Models\FloosakWallet;

class FloosakProvider extends AbstractPaymentProvider
{
    const SERVICE_URL = "https://staging.fintech-expert.net";
    const GENERATE_KEY_ENDPOINT = "/api/v1/request/key";
    const VERIFY_KEY_ENDPOINT = "/api/v1/verify/key";
    const PURCHASE_ENDPOINT = "/api/v1/merchant/p2mcl";

    private $apiKey;
    private $purchaseId;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(["short_code", "phone_number"]);
    }

    public function getProviderName(): string
    {
        return "floosak";
    }

    public function getSupportedCurrencies(): array
    {
        return FloosakWallet::pluck('currency_symbol')->toArray();
    }

    public function authenticate(): void
    {
        try {
            // Check if API key is valid from database
            $apiKey = ApiKeys::where("service", "floosak")->first();

            if (!$apiKey || $apiKey->expiry_date < now()) {
                throw new Exception(
                    "Service is not available - API key expired or missing",
                );
            }

            $this->apiKey = $this->getConfig("api_key");
            $this->logInfo("Authentication successful - API key validated");
        } catch (Exception $e) {
            $this->logError("Authentication failed", [
                "error" => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function makePayment(
        string $userPhone,
        string $requestId,
        string $code,
        float $amount,
        string $currencyCode,
        array $additionalData = [],
    ): PaymentResponse {
        try {
            $this->ensureAuthenticated();

            // For Floosak, the code is actually the OTP for confirming purchase
            // So we need to handle this differently - we assume purchase was already requested
            $purchaseId = $additionalData["purchase_id"] ?? null;

            if (!$purchaseId) {
                return $this->createErrorResponse(
                    "Purchase ID is required for Floosak payment confirmation",
                );
            }

            $response = $this->confirmPurchase($purchaseId, $code);

            if ($response->is_success) {
                return $this->createSuccessResponse(
                    $response->message ?? "Payment completed successfully",
                    $response->transaction_id ?? $purchaseId,
                    $response->reference_id ?? null,
                    $amount,
                    $currencyCode,
                    "completed",
                    (array) $response,
                );
            } else {
                return $this->createErrorResponse(
                    $response->message ?? "Payment failed",
                    (array) $response,
                );
            }
        } catch (Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }

    public function checkPaymentStatus(string $requestId): PaymentResponse
    {
        try {
            // Floosak doesn't have a direct status check endpoint
            // This would need to be implemented based on their actual API
            // For now, return a generic response
            return $this->createSuccessResponse(
                "Status check not implemented for Floosak",
                $requestId,
                null,
                null,
                null,
                "unknown",
                ["message" => "Status check endpoint not available"],
            );
        } catch (Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }

    public function refundPayment(
        string $requestId,
        string $referenceId,
        float $amount,
        string $currencyCode,
    ): PaymentResponse {
        try {
            // Floosak refund functionality would need to be implemented
            // based on their actual API capabilities
            return $this->createErrorResponse(
                "Refund functionality not implemented for Floosak",
                ["message" => "Refund endpoint not available"],
            );
        } catch (Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }

    public function validatePaymentCode(
        string $code,
        string $requestId,
        string $userPhone,
        float $amount,
        string $currencyCode,
    ): PaymentResponse {
        try {
            $this->ensureAuthenticated();

            // For Floosak, validation involves requesting a purchase first
            $sourceWalletId = $this->getSourceWalletId($currencyCode);
            $purpose = "Payment validation for request: {$requestId}";

            $response = $this->requestPurchase(
                $sourceWalletId,
                $requestId,
                $userPhone,
                $amount,
                $purpose,
            );

            if ($response->is_success) {
                return $this->createSuccessResponse(
                    "Payment code validation initiated - OTP sent",
                    null,
                    null,
                    $amount,
                    $currencyCode,
                    "validation_pending",
                    (array) $response,
                    [
                        "purchase_id" => $response->purchase_id,
                        "otp_required" => true,
                        "message" =>
                            "OTP has been sent to complete the payment",
                    ],
                );
            } else {
                return $this->createErrorResponse(
                    $response->message ?? "Payment validation failed",
                    (array) $response,
                );
            }
        } catch (Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }

    /**
     * Generate API key request - This would be called separately for initial setup
     */
    public function generateKeyRequest(): array
    {
        try {
            $params = [
                "short_code" => $this->getConfig("short_code"),
                "phone" => $this->getConfig("phone_number"),
            ];

            $headers = [
                "Accept: application/json",
                "Content-Type: application/json",
                "x-channel: merchant",
            ];

            $response = $this->makeHttpRequest(
                "POST",
                self::SERVICE_URL . self::GENERATE_KEY_ENDPOINT,
                $headers,
                json_encode($params),
            );

            $responseData = $this->parseJsonResponse($response["body"]);

            if (!isset($responseData["request_id"])) {
                throw new Exception(
                    "Failed to get request id: " .
                        ($responseData["message"] ?? "Unknown error"),
                );
            }

            $this->logInfo("Key generation request successful", [
                "request_id" => $responseData["request_id"],
            ]);
            return $responseData;
        } catch (Exception $e) {
            $this->logError("Key generation request failed", [
                "error" => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify the generated key with OTP - This would be called separately for initial setup
     */
    public function verifyGeneratedKey(string $requestId, string $otp): object
    {
        try {
            $params = [
                "request_id" => $requestId,
                "otp" => $otp,
            ];

            $headers = [
                "Accept: application/json",
                "Content-Type: application/json",
                "x-channel: merchant",
            ];

            $response = $this->makeHttpRequest(
                "POST",
                self::SERVICE_URL . self::VERIFY_KEY_ENDPOINT,
                $headers,
                json_encode($params),
            );

            $responseData = $this->parseJsonResponse($response["body"], false);

            if (!isset($responseData->key)) {
                throw new Exception(
                    "Failed to verify key: " .
                        ($responseData->message ?? "Unknown error"),
                );
            }

            $this->logInfo("Key verification successful");
            return $responseData;
        } catch (Exception $e) {
            $this->logError("Key verification failed", [
                "error" => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Request a purchase transaction
     */
    private function requestPurchase(
        string $sourceWalletId,
        string $requestId,
        string $targetPhone,
        float $amount,
        string $purpose = "",
    ): object {
        $params = [
            "source_wallet_id" => $sourceWalletId,
            "request_id" => $requestId,
            "target_phone" => $targetPhone,
            "amount" => $this->formatAmount($amount),
            "purpose" => $purpose,
        ];

        $headers = [
            "Authorization: Bearer " . $this->apiKey,
            "Accept: application/json",
            "Content-Type: application/json",
            "x-channel: merchant",
        ];

        $this->logDebug("Requesting purchase", [
            "request_id" => $requestId,
            "amount" => $amount,
        ]);

        $response = $this->makeHttpRequest(
            "POST",
            self::SERVICE_URL . self::PURCHASE_ENDPOINT,
            $headers,
            json_encode($params),
        );

        $responseData = $this->parseJsonResponse($response["body"]);

        if (
            !isset($responseData["is_success"]) ||
            $responseData["is_success"] == false
        ) {
            throw new Exception(
                "Failed to request purchase: " .
                    ($responseData["message"] ?? "Unknown error"),
            );
        }

        return (object) $responseData;
    }

    /**
     * Confirm a purchase transaction with OTP
     */
    private function confirmPurchase(string $purchaseId, string $otp): object
    {
        $params = [
            "otp" => $otp,
            "purchase_id" => $purchaseId,
        ];

        $headers = [
            "Accept: application/json",
            "Authorization: Bearer " . $this->apiKey,
            "Content-Type: application/json",
            "x-channel: merchant",
        ];

        $this->logDebug("Confirming purchase", ["purchase_id" => $purchaseId]);

        $response = $this->makeHttpRequest(
            "POST",
            self::SERVICE_URL . self::PURCHASE_ENDPOINT,
            $headers,
            json_encode($params),
        );

        $responseData = $this->parseJsonResponse($response["body"]);

        if (
            !isset($responseData["is_success"]) ||
            $responseData["is_success"] == false
        ) {
            throw new Exception(
                "Failed to confirm purchase: " .
                    ($responseData["message"] ?? "Unknown error"),
            );
        }

        return (object) $responseData;
    }

    /**
     * Get source wallet ID - this might come from configuration or database
     */
    private function getSourceWalletId(string $currencyCode): string
    {
       $wallet = FloosakWallet::where('currency_symbol', $currencyCode)->first();

        if($wallet){
            return $wallet->wallet_id;
        }
        return $this->getConfig("source_wallet_id", "default_wallet_id");
    }

    /**
     * Override the ensureAuthenticated method to include API key validation
     */
    protected function ensureAuthenticated(): void
    {
        if (!$this->apiKey) {
            $this->authenticate();
        }

        // Additional check for API key validity
        $apiKey = ApiKeys::where("service", "floosak")->first();
        if (!$apiKey || $apiKey->expiry_date < now()) {
            throw new Exception("Service is not available - API key expired");
        }
    }
}
