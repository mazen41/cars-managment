<?php

namespace App\Services\Payment\Providers;

use App\Services\Payment\Providers\AbstractPaymentProvider;
use App\Contracts\Payment\PaymentResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class JawaliProvider extends AbstractPaymentProvider
{
    const URL = "https://app.wecash.com.ye:8493";
    const LOGIN_ENDPOINT = "/paygate/oauth/token";
    const PAYMENT_ENDPOINT = "/paygate/v1/ws/callWS";

    private $loginAccessToken;
    private $loginTokenExpiryTime;
    private $authAccessToken;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['username', 'password', 'orgid', 'agent_id', 'agent_pwd']);
    }

    public function getProviderName(): string
    {
        return 'jawali';
    }

    public function getSupportedCurrencies(): array
    {
        return ['YER', 'USD', 'SAR'];
    }

    public function authenticate(): void
    {
        try {
            $this->login();
            $this->authenticateAgent();
            $this->logInfo('Authentication successful');
        } catch (Exception $e) {
            $this->logError('Authentication failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function makePayment(
        string $userPhone,
        string $requestId,
        string $code,
        float $amount,
        string $currencyCode,
        array $additionalData = []
    ): PaymentResponse {
        try {
            if(empty($code)){
                return $this->createErrorResponse('Purchase Code is required');
            }

            $this->ensureAuthenticated();

            $phoneWithoutCode = $this->normalizePhoneNumber($userPhone);

            $params = [
                "header" => [
                    "serviceDetail" => [
                        "corrID" => "59ba381c-1f5f-4480-90cc-0660b9cc850e",
                        "domainName" => "MerchantDomain",
                        "serviceName" => "PAYAG.ECOMMCASHOUT"
                    ],
                    "signonDetail" => [
                        "clientID" => "WeCash",
                        "orgID" => $this->getConfig('orgid'),
                        "userID" => $this->getConfig('username'),
                        "externalUser" => "webapp"
                    ],
                    "messageContext" => [
                        "clientDate" => time(),
                        "bodyType" => "Clear"
                    ]
                ],
                "body" => [
                    "agentWallet" => $this->getConfig('agent_id'),
                    "password" => $this->getConfig('agent_pwd'),
                    "txncurrency" => $currencyCode,
                    "voucher" => $code,
                    "receiverMobile" => $phoneWithoutCode,
                    "accessToken" => $this->authAccessToken,
                    "refId" => $requestId,
                    "purpose" => $additionalData['purpose'] ?? 'Payment for Samh code:' . $requestId,
                ]
            ];

            $requestHeaders = [
                "Authorization: Bearer " . $this->loginAccessToken,
                "Content-Type: application/json"
            ];

            $this->logDebug('Making payment request', ['request_id' => $requestId, 'amount' => $amount]);

            $response = $this->makeHttpRequest(
                'POST',
                self::URL . self::PAYMENT_ENDPOINT,
                $requestHeaders,
                json_encode($params)
            );

            $responseData = $this->parseJsonResponse($response['body']);

            if (isset($responseData['responseBody']['responseCode']) && $responseData['responseBody']['responseCode'] === '000') {
                return $this->createSuccessResponse(
                    $responseData['responseBody']['responseMessage'] ?? 'Payment completed successfully',
                    $responseData['responseBody']['transactionId'] ?? $requestId,
                    $responseData['responseBody']['referenceId'] ?? null,
                    $amount,
                    $currencyCode,
                    'completed',
                    $responseData
                );
            } else {
                $errorMessage = $responseData['responseBody']['responseMessage'] ?? 'Payment failed';
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

            // For Jawali, we can use the same payment structure but for status inquiry
            // This is a simplified implementation - you may need to adjust based on actual API
            $params = [
                "header" => [
                    "serviceDetail" => [
                        "corrID" => "59ba381c-1f5f-4480-90cc-0660b9cc850e",
                        "domainName" => "MerchantDomain",
                        "serviceName" => "PAYAG.TRANSACTIONSTATUS"
                    ],
                    "signonDetail" => [
                        "clientID" => "WeCash",
                        "orgID" => $this->getConfig('orgid'),
                        "userID" => $this->getConfig('username'),
                        "externalUser" => "webapp"
                    ],
                    "messageContext" => [
                        "clientDate" => time(),
                        "bodyType" => "Clear"
                    ]
                ],
                "body" => [
                    "agentWallet" => $this->getConfig('agent_id'),
                    "password" => $this->getConfig('agent_pwd'),
                    "accessToken" => $this->authAccessToken,
                    "refId" => $requestId,
                ]
            ];

            $requestHeaders = [
                "Authorization: Bearer " . $this->loginAccessToken,
                "Content-Type: application/json"
            ];

            $response = $this->makeHttpRequest(
                'POST',
                self::URL . self::PAYMENT_ENDPOINT,
                $requestHeaders,
                json_encode($params)
            );

            $responseData = $this->parseJsonResponse($response['body']);

            if (isset($responseData['responseBody']['responseCode']) && $responseData['responseBody']['responseCode'] === '000') {
                return $this->createSuccessResponse(
                    'Status retrieved successfully',
                    $responseData['responseBody']['transactionId'] ?? $requestId,
                    $responseData['responseBody']['referenceId'] ?? null,
                    $responseData['responseBody']['amount'] ?? null,
                    $responseData['responseBody']['currency'] ?? null,
                    $responseData['responseBody']['status'] ?? 'completed',
                    $responseData
                );
            } else {
                $errorMessage = $responseData['responseBody']['responseMessage'] ?? 'Status check failed';
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
        string $currencyCode
    ): PaymentResponse {
        try {
            $this->ensureAuthenticated();

            $params = [
                "header" => [
                    "serviceDetail" => [
                        "corrID" => "59ba381c-1f5f-4480-90cc-0660b9cc850e",
                        "domainName" => "MerchantDomain",
                        "serviceName" => "PAYAG.REFUND"
                    ],
                    "signonDetail" => [
                        "clientID" => "WeCash",
                        "orgID" => $this->getConfig('orgid'),
                        "userID" => $this->getConfig('username'),
                        "externalUser" => "webapp"
                    ],
                    "messageContext" => [
                        "clientDate" => time(),
                        "bodyType" => "Clear"
                    ]
                ],
                "body" => [
                    "agentWallet" => $this->getConfig('agent_id'),
                    "password" => $this->getConfig('agent_pwd'),
                    "txncurrency" => $currencyCode,
                    "amount" => $this->formatAmount($amount),
                    "accessToken" => $this->authAccessToken,
                    "refId" => $requestId,
                    "referenceId" => $referenceId,
                ]
            ];

            $requestHeaders = [
                "Authorization: Bearer " . $this->loginAccessToken,
                "Content-Type: application/json"
            ];

            $response = $this->makeHttpRequest(
                'POST',
                self::URL . self::PAYMENT_ENDPOINT,
                $requestHeaders,
                json_encode($params)
            );

            $responseData = $this->parseJsonResponse($response['body']);

            if (isset($responseData['responseBody']['responseCode']) && $responseData['responseBody']['responseCode'] === '000') {
                return $this->createSuccessResponse(
                    'Refund processed successfully',
                    $responseData['responseBody']['transactionId'] ?? null,
                    $responseData['responseBody']['referenceId'] ?? $referenceId,
                    $amount,
                    $currencyCode,
                    'refunded',
                    $responseData
                );
            } else {
                $errorMessage = $responseData['responseBody']['responseMessage'] ?? 'Refund failed';
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
        string $currencyCode
    ): PaymentResponse {
        try {
            $this->ensureAuthenticated();

            $phoneWithoutCode = $this->normalizePhoneNumber($userPhone);

            $params = [
                "header" => [
                    "serviceDetail" => [
                        "corrID" => "59ba381c-1f5f-4480-90cc-0660b9cc850e",
                        "domainName" => "MerchantDomain",
                        "serviceName" => "PAYAG.ECOMMERCEINQUIRY"
                    ],
                    "signonDetail" => [
                        "clientID" => "WeCash",
                        "orgID" => $this->getConfig('orgid'),
                        "userID" => $this->getConfig('username'),
                        "externalUser" => "webapp"
                    ],
                    "messageContext" => [
                        "clientDate" => time(),
                        "bodyType" => "Clear"
                    ]
                ],
                "body" => [
                    "agentWallet" => $this->getConfig('agent_id'),
                    "password" => $this->getConfig('agent_pwd'),
                    "txncurrency" => $currencyCode,
                    "voucher" => $code,
                    "receiverMobile" => $phoneWithoutCode,
                    "accessToken" => $this->authAccessToken,
                    "refId" => $requestId,
                    "purpose" => ""
                ]
            ];

            $requestHeaders = [
                "Authorization: Bearer " . $this->loginAccessToken,
                "Content-Type: application/json"
            ];

            $this->logDebug('Validating payment code', ['request_id' => $requestId, 'code' => substr($code, 0, 4) . '***']);

            $response = $this->makeHttpRequest(
                'POST',
                self::URL . self::PAYMENT_ENDPOINT,
                $requestHeaders,
                json_encode($params)
            );

            $responseData = $this->parseJsonResponse($response['body']);

            if (isset($responseData['responseBody']['responseCode']) && $responseData['responseBody']['responseCode'] === '000') {
                return $this->createSuccessResponse(
                    'Payment code is valid',
                    null,
                    null,
                    $responseData['responseBody']['amount'] ?? $amount,
                    $currencyCode,
                    'validated',
                    $responseData,
                    [
                        'validated_amount' => $responseData['responseBody']['amount'] ?? $amount,
                        'voucher_details' => $responseData['responseBody'] ?? []
                    ]
                );
            } else {
                $errorMessage = $responseData['responseBody']['responseMessage'] ?? 'Payment code validation failed';
                return $this->createErrorResponse($errorMessage, $responseData);
            }
        } catch (Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }

    private function login(): void
    {
        if ($this->isLoginTokenValid()) {
            return;
        }

        $params = [
            'grant_type' => 'password',
            'client_id' => 'restapp',
            'client_secret' => 'restapp',
            'scope' => 'read',
            'username' => $this->getConfig('username'),
            'password' => $this->getConfig('password'),
        ];

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $response = $this->makeHttpRequest(
            'POST',
            self::URL . self::LOGIN_ENDPOINT,
            $headers,
            http_build_query($params)
        );

        $tokenResponse = $this->parseJsonResponse($response['body']);

        if (!isset($tokenResponse['access_token'])) {
            $this->logError('Failed to get login access token', $tokenResponse);
            throw new Exception('Failed to get access token: ' . json_encode($tokenResponse));
        }

        $this->loginAccessToken = $tokenResponse['access_token'];
        $this->loginTokenExpiryTime = time() + ($tokenResponse['expires_in'] ?? 3600);

        // Cache the login token
        Cache::put('jawali_login_access_token', $this->loginAccessToken, $tokenResponse['expires_in'] ?? 3600);
        Cache::put('jawali_login_token_expiry_time', $this->loginTokenExpiryTime, $tokenResponse['expires_in'] ?? 3600);

        $this->logInfo('Login successful', ['expires_in' => $tokenResponse['expires_in'] ?? 3600]);
    }

    private function isLoginTokenValid(): bool
    {
        if ($this->loginAccessToken && $this->loginTokenExpiryTime && time() < $this->loginTokenExpiryTime) {
            return true;
        }

        // Attempt to fetch from cache
        $this->loginAccessToken = Cache::get('jawali_login_access_token');
        $this->loginTokenExpiryTime = Cache::get('jawali_login_token_expiry_time');

        return $this->loginAccessToken && $this->loginTokenExpiryTime && time() < $this->loginTokenExpiryTime;
    }

    private function authenticateAgent(): void
    {
        $params = [
            "header" => [
                "serviceDetail" => [
                    "corrID" => "59ba381c-1f5f-4480-90cc-0660b9cc850e",
                    "domainName" => "WalletDomain",
                    "serviceName" => "PAYWA.WALLETAUTHENTICATION"
                ],
                "signonDetail" => [
                    "clientID" => "WeCash",
                    "orgID" => $this->getConfig('orgid'),
                    "userID" => $this->getConfig('username'),
                    "externalUser" => 'bab-almandab'
                ],
                "messageContext" => [
                    "clientDate" => time(),
                    "bodyType" => "Clear"
                ]
            ],
            "body" => [
                'identifier' => $this->getConfig('agent_id'),
                'password' => $this->getConfig('agent_pwd'),
            ]
        ];

        $requestHeaders = [
            "Authorization: Bearer " . $this->loginAccessToken,
            "Content-Type: application/json"
        ];

        $response = $this->makeHttpRequest(
            'POST',
            self::URL . self::PAYMENT_ENDPOINT,
            $requestHeaders,
            json_encode($params)
        );

        $responseData = $this->parseJsonResponse($response['body']);

        if (!isset($responseData['responseBody']['access_token'])) {
            $this->logError('Agent authentication failed', $responseData);
            throw new Exception('Authentication failed: ' . json_encode($responseData));
        }

        $this->authAccessToken = $responseData['responseBody']['access_token'];
        $this->logInfo('Agent authentication successful');
    }
}
