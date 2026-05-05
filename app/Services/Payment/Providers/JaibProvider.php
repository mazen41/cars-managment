<?php

namespace App\Services\Payment\Providers;

use App\Services\Payment\Providers\AbstractPaymentProvider;
use App\Contracts\Payment\PaymentResponse;
use Exception;

class JaibProvider extends AbstractPaymentProvider
{
    const URL = "https://www.api2.e-jaib.com:5088";
    const PAYMENT_ENDPOINT = "/api/v1/BuyOnline/ExeBuy";
    const LOGIN_ENDPOINT = "/api/v1/TokenAuth/LogAPI";
    const REFUND_ENDPOINT = "/api/v1/BuyOnline/RefoundBuy";
    const CHECK_PROGRESS_ENDPOINT = "/api/v1/BuyOnline/CheckProgress";
    const CHANGE_PASSWORD = "/api/v1/Others/ChangePasswordAgent";

    protected string $pinApi = "";

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(["user", "pass", "agent_code"]);
    }

    public function getProviderName(): string
    {
        return "jaib";
    }

     public function getSupportedCurrencies(): array
    {
        return ['YER', 'USD', 'SAR'];
    }

    public function authenticate(): void
    {
        // Check if we already have a valid token
        if ($this->isTokenValid()) {
            return;
        }

        $params = [
            "userName" => $this->getConfig("user"),
            "password" => $this->getConfig("pass"),
            "agentCode" => $this->getConfig("agent_code"),
        ];

        $serviceURL = self::URL . self::LOGIN_ENDPOINT;
        $headers = ["Content-Type: application/json"];

        try {
            $response = $this->makeHttpRequest(
                "POST",
                $serviceURL,
                $headers,
                json_encode($params),
            );

            if (!$response["success"]) {
                throw new Exception(
                    "HTTP request failed with code: " . $response["http_code"],
                );
            }

            $tokenResponse = $this->parseJsonResponse($response["body"]);

            if (!($tokenResponse["success"] ?? false)) {
                $errorMessage =
                    $tokenResponse["error"]["message"] ??
                    "Failed to get access token";
                throw new Exception($errorMessage);
            }

            $result = $tokenResponse["result"];
            $accessToken = $result["accessToken"];
            $this->pinApi = $result["pinApi"];
            $expirySeconds = $result["expire"];

            // Cache the token and pinApi
            $this->cacheToken($accessToken, $expirySeconds, [
                "pin_api" => $this->pinApi,
            ]);

            $this->logInfo("Authentication successful");
        } catch (Exception $e) {
            $this->logError("Authentication failed: " . $e->getMessage());
            throw new Exception(
                "Failed to authenticate with Jaib API: " . $e->getMessage(),
            );
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
            if(empty($code)){
                return $this->createErrorResponse('Purchase Code is required');
            }
            $this->ensureAuthenticated();

            $params = [
                "pinApi" => $this->getPinApi(),
                "mobile" => $userPhone,
                "amount" => $this->formatAmount($amount),
                "currencyCode" => $currencyCode,
                "code" => $code,
                "requestID" => $requestId,
                "notes" =>
                    $additionalData["notes"] ??
                    "Payment for order code: {$requestId}",
            ];

            $headers = [
                "Authorization: Bearer " . $this->accessToken,
                "Content-Type: application/json",
            ];

            $response = $this->makeHttpRequest(
                "POST",
                self::URL . self::PAYMENT_ENDPOINT,
                $headers,
                json_encode($params),
            );

            if (!$response["success"]) {
                return $this->createErrorResponse(
                    "HTTP request failed with code: " . $response["http_code"],
                );
            }

            $responseData = $this->parseJsonResponse($response["body"]);

            if ($responseData["success"] ?? false) {
                return $this->createSuccessResponse(
                    $responseData["result"]["msg"] ?? "Payment successful",
                    $responseData["result"]["transactionId"] ?? null,
                    $responseData["result"]["referenceId"] ?? null,
                    $amount,
                    $currencyCode,
                    "completed",
                    $responseData,
                );
            } else {
                $errorMessage =
                    $responseData["error"]["message"] ?? "Payment failed";
                return $this->createErrorResponse($errorMessage, $responseData);
            }
        } catch (Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }

    public function checkPaymentStatus(string $requestId): PaymentResponse
    {
        try {
            $this->ensureAuthenticated();

            $params = [
                "pinApi" => $this->getPinApi(),
                "requestID" => $requestId,
            ];

            $headers = [
                "Authorization: Bearer " . $this->accessToken,
                "Content-Type: application/json",
            ];

            $response = $this->makeHttpRequest(
                "POST",
                self::URL . self::CHECK_PROGRESS_ENDPOINT,
                $headers,
                json_encode($params),
            );

            if (!$response["success"]) {
                return $this->createErrorResponse(
                    "HTTP request failed with code: " . $response["http_code"],
                );
            }

            $responseData = $this->parseJsonResponse($response["body"]);

            if ($responseData["success"] ?? false) {
                $result = $responseData["result"] ?? [];
                return $this->createSuccessResponse(
                    "Status check successful",
                    $result["transactionId"] ?? null,
                    $result["referenceId"] ?? null,
                    $result["amount"] ?? null,
                    $result["currency"] ?? null,
                    $result["status"] ?? "unknown",
                    $responseData,
                );
            } else {
                $errorMessage =
                    $responseData["error"]["message"] ?? "Status check failed";
                return $this->createErrorResponse($errorMessage, $responseData);
            }
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
            $this->ensureAuthenticated();

            $params = [
                "pinApi" => $this->getPinApi(),
                "amount" => $this->formatAmount($amount),
                "currencyCode" => $currencyCode,
                "referenceID" => $referenceId,
                "requestID" => $requestId,
                "notes" => "",
            ];

            $headers = [
                "Authorization: Bearer " . $this->accessToken,
                "Content-Type: application/json",
            ];

            $response = $this->makeHttpRequest(
                "POST",
                self::URL . self::REFUND_ENDPOINT,
                $headers,
                json_encode($params),
            );

            if (!$response["success"]) {
                return $this->createErrorResponse(
                    "HTTP request failed with code: " . $response["http_code"],
                );
            }

            $responseData = $this->parseJsonResponse($response["body"]);

            if ($responseData["success"] ?? false) {
                return $this->createSuccessResponse(
                    "Refund successful",
                    $responseData["result"]["transactionId"] ?? null,
                    $responseData["result"]["referenceId"] ?? null,
                    $amount,
                    $currencyCode,
                    "refunded",
                    $responseData,
                );
            } else {
                $errorMessage =
                    $responseData["error"]["message"] ?? "Refund failed";
                return $this->createErrorResponse($errorMessage, $responseData);
            }
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
        // Jaib doesn't have a separate validation endpoint, so we'll return a generic success
        // The actual validation happens during payment
        return $this->createSuccessResponse(
            "Code validation not available for Jaib provider",
        );
    }

    public function changePassword(
        string $oldPassword,
        string $newPassword,
    ): PaymentResponse {
        try {
            $this->ensureAuthenticated();

            $params = [
                "currentPassword" => $oldPassword,
                "newPassword" => $newPassword,
            ];

            $headers = [
                "Authorization: Bearer " . $this->accessToken,
                "Content-Type: application/json",
            ];

            $response = $this->makeHttpRequest(
                "POST",
                self::URL . self::CHANGE_PASSWORD,
                $headers,
                json_encode($params),
            );

            if (!$response["success"]) {
                return $this->createErrorResponse(
                    "HTTP request failed with code: " . $response["http_code"],
                );
            }

            $responseData = $this->parseJsonResponse($response["body"]);

            if ($responseData["success"] ?? false) {
                return $this->createSuccessResponse(
                    "Password changed successfully",
                    null,
                    null,
                    null,
                    null,
                    "completed",
                    $responseData,
                );
            } else {
                $errorMessage =
                    $responseData["error"]["message"] ??
                    "Password change failed";
                return $this->createErrorResponse($errorMessage, $responseData);
            }
        } catch (Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }

    /**
     * Get PIN API from cache or authenticate
     */
    private function getPinApi(): string
    {
        if (!empty($this->pinApi)) {
            return $this->pinApi;
        }

        // Try to get from cache
        $cacheKey = $this->getTokenCacheKey() . "_pin_api";
        $cachedPinApi = cache()->get($cacheKey);

        if ($cachedPinApi && $this->isTokenValid()) {
            $this->pinApi = $cachedPinApi;
            return $this->pinApi;
        }

        // Re-authenticate to get fresh PIN API
        $this->authenticate();
        return $this->pinApi;
    }

    // Legacy static methods for backward compatibility
    public static function getAccessToken()
    {
        $instance = new self();
        $instance->authenticate();

        return (object) [
            "access_token" => $instance->accessToken,
            "pinApi" => $instance->getPinApi(),
        ];
    }

    public static function make_payment(
        $user_phone,
        $requestID,
        $code,
        $amount,
        $currency_code,
    ) {
        $instance = new self();
        $response = $instance->makePayment(
            $user_phone,
            $requestID,
            $code,
            $amount,
            $currency_code,
        );

        // Convert to legacy format
        return (object) [
            "success" => $response->success,
            "result" => (object) ["msg" => $response->message],
            "error" => (object) ["message" => $response->message],
        ];
    }

    public static function refund(
        $amount,
        $requestID,
        $referenceID,
        $currency_code,
    ) {
        $instance = new self();
        $response = $instance->refundPayment(
            $requestID,
            $referenceID,
            $amount,
            $currency_code,
        );

        // Convert to legacy format
        return (object) [
            "success" => $response->success,
            "result" => (object) ["msg" => $response->message],
            "error" => (object) ["message" => $response->message],
        ];
    }

    public static function check($requestID)
    {
        $instance = new self();
        $response = $instance->checkPaymentStatus($requestID);

        // Convert to legacy format
        return (object) [
            "success" => $response->success,
            "result" => (object) ["msg" => $response->message],
            "error" => (object) ["message" => $response->message],
        ];
    }

    public static function change_password($old_pass, $new_pass)
    {
        $instance = new self();
        $response = $instance->changePassword($old_pass, $new_pass);

        // Convert to legacy format
        return (object) [
            "success" => $response->success,
            "result" => (object) ["msg" => $response->message],
            "error" => (object) ["message" => $response->message],
        ];
    }

    public static function invokeCurlRequest($type, $url, $headers, $post)
    {
        $instance = new self();
        try {
            $response = $instance->makeHttpRequest(
                $type,
                $url,
                $headers,
                $post,
            );
            return $response["success"] ? $response["body"] : false;
        } catch (Exception $e) {
            return false;
        }
    }
}
