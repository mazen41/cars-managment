<?php
namespace App\Utility\PaymentUtility;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class JawaliUtility
{
    const URL = "https://app.wecash.com.ye:8493";
    const LOGIN_ENDPOINT = "/paygate/oauth/token";
    const PAYMENT_ENDPOINT = "/paygate/v1/ws/callWS";

    private $loginAccessToken;
    private $loginTokenExpiryTime;
    private $authAccessToken;

    public function __construct()
    {
        $this->login();
        $this->authenticate();
    }

    private function login()
    {
        if ($this->isLoginTokenValid()) {
            return;
        }

        $params = [
            'grant_type' => 'password',
            'client_id' => 'restapp',
            'client_secret' => 'restapp',
            'scope' => 'read',
            'username' => env('JAWALI_USERNAME'),
            'password' => env('JAWALI_PASS'),
        ];

        $serviceURL = self::URL . self::LOGIN_ENDPOINT;
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $tokenResponse = $this->invokeCurlRequest("POST", $serviceURL, $headers, http_build_query($params));

        if ($tokenResponse === false) {
            throw new Exception('Failed to connect to Jawali API for token.');
        }

        $tokenResponse = json_decode($tokenResponse);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Jawali API.');
        }

        if (!isset($tokenResponse->access_token)) {
            Log::error(json_encode($tokenResponse));
            throw new Exception('Failed to get access token: ' . json_encode($tokenResponse));
        }

        $this->loginAccessToken = $tokenResponse->access_token;
        $this->loginTokenExpiryTime = time() + $tokenResponse->expires_in;

        // Optionally cache the token and expiry time
        Cache::put('jawali_login_access_token', $this->loginAccessToken, $tokenResponse->expires_in);
        Cache::put('jawali_login_token_expiry_time', $this->loginTokenExpiryTime, $tokenResponse->expires_in);
    }

    private function isLoginTokenValid()
    {
        if ($this->loginAccessToken && $this->loginTokenExpiryTime && time() < $this->loginTokenExpiryTime) {
            return true;
        }

        // Attempt to fetch from cache if not already set
        $this->loginAccessToken = Cache::get('jawali_login_access_token');
        $this->loginTokenExpiryTime = Cache::get('jawali_login_token_expiry_time');

        return $this->loginAccessToken && $this->loginTokenExpiryTime && time() < $this->loginTokenExpiryTime;
    }

    private function authenticate()
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
                    "orgID" => env('JAWALI_ORGID'),
                    "userID" => env('JAWALI_USERNAME'),
                    "externalUser" => 'bab-almandab'
                ],
                "messageContext" => [
                    "clientDate" => time(),
                    "bodyType" => "Clear"
                ]
            ],
            "body" => [
                'identifier' => env('JAWALI_AGENT_ID'),
                'password' => env('JAWALI_AGENT_PWD'),
            ]
        ];

        $requestHeaders = [
            "Authorization: Bearer " . $this->loginAccessToken,
            "Content-Type: application/json"
        ];

        $requestResponse = $this->invokeCurlRequest("POST", self::URL . self::PAYMENT_ENDPOINT, $requestHeaders, json_encode($params));

        if ($requestResponse === false) {
            throw new Exception('Failed to connect to Jawali API for authentication.');
        }

        $response = json_decode($requestResponse);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Jawali API.');
        }

        if (!isset($response->responseBody->access_token)) {
            Log::error(json_encode($response));
            throw new Exception('Authentication failed: ' . json_encode($response));
        }

        $this->authAccessToken = $response->responseBody->access_token;
    }

    public function enquiry($voucher, $requestID, $user_phone, $currency)
    {
        $this->checkAndRefreshTokens();
        $phoneWithoutCode = str_replace("+967", "", $user_phone);
        $params = [
            "header" => [
                "serviceDetail" => [
                    "corrID" => "59ba381c-1f5f-4480-90cc-0660b9cc850e",
                    "domainName" => "MerchantDomain",
                    "serviceName" => "PAYAG.ECOMMERCEINQUIRY"
                ],
                "signonDetail" => [
                    "clientID" => "WeCash",
                    "orgID" => env('JAWALI_ORGID'),
                    "userID" => env('JAWALI_USERNAME'),
                    "externalUser" => "webapp"
                ],
                "messageContext" => [
                    "clientDate" => time(),
                    "bodyType" => "Clear"
                ]
            ],
            "body" => [
                "agentWallet" => env('JAWALI_AGENT_ID'),
                "password" => env('JAWALI_AGENT_PWD'),
                "txncurrency" => $currency,
                "voucher" => $voucher,
                "receiverMobile" => $phoneWithoutCode,
                "accessToken" => $this->authAccessToken,
                "refId" => $requestID,
                "purpose" => ""
            ]
        ];

        $requestHeaders = [
            "Authorization: Bearer " . $this->loginAccessToken,
            "Content-Type: application/json"
        ];
        log::info(json_encode($params));
        $requestResponse = $this->invokeCurlRequest("POST", self::URL . self::PAYMENT_ENDPOINT, $requestHeaders, json_encode($params));
        log::info($requestResponse);
        if ($requestResponse === false) {
            throw new Exception('Failed to connect to Jawali API for enquiry.');
        }

        $response = json_decode($requestResponse);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Jawali API.');
        }

        return $response;
    }

    public function make_payment($user_phone, $requestID, $code, $currency)
    {
        $this->checkAndRefreshTokens();
        $phoneWithoutCode = str_replace("+967", "", $user_phone);
        $params = [
            "header" => [
                "serviceDetail" => [
                    "corrID" => "59ba381c-1f5f-4480-90cc-0660b9cc850e",
                    "domainName" => "MerchantDomain",
                    "serviceName" => "PAYAG.ECOMMCASHOUT"
                ],
                "signonDetail" => [
                    "clientID" => "WeCash",
                    "orgID" => env('JAWALI_ORGID'),
                    "userID" => env('JAWALI_USERNAME'),
                    "externalUser" => "webapp"
                ],
                "messageContext" => [
                    "clientDate" => time(),
                    "bodyType" => "Clear"
                ]
            ],
            "body" => [
                "agentWallet" => env('JAWALI_AGENT_ID'),
                "password" => env('JAWALI_AGENT_PWD'),
                "txncurrency" => $currency,
                "voucher" => $code,
                "receiverMobile" => $phoneWithoutCode,
                "accessToken" => $this->authAccessToken,
                "refId" => $requestID,
                "purpose" => 'Payment for Samh code:' . $requestID,
            ]
        ];

        $requestHeaders = [
            "Authorization: Bearer " . $this->loginAccessToken,
            "Content-Type: application/json"
        ];
        log::info(json_encode($params));
        $requestResponse = $this->invokeCurlRequest("POST", self::URL . self::PAYMENT_ENDPOINT, $requestHeaders, json_encode($params));

        log::info($requestResponse);
        if ($requestResponse === false) {
            throw new Exception('Failed to connect to Jawali API for payment.');
        }

        $response = json_decode($requestResponse);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Jawali API.');
        }

        return $response;
    }

    private function checkAndRefreshTokens()
    {
        // Ensure login token is valid
        $this->login();

    }

    private function invokeCurlRequest($type, $url, $headers, $post)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($type == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $server_output = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = 'Curl error: ' . curl_error($ch);
            Log::error($error_msg);
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return $server_output;
    }
}
