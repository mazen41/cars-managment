<?php

namespace App\Utility\PaymentUtility;

use Exception;
use Illuminate\Support\Facades\Log;

class JaibUtility
{
    const URL = "https://www.api2.e-jaib.com:5088";
    const PAYMENT_ENDPOINT = "/api/v1/BuyOnline/ExeBuy";
    const LOGIN_ENDPOINT = "/api/v1/TokenAuth/LogAPI";
    const REFUND_ENDPOINT ="/api/v1/BuyOnline/RefoundBuy";
    const CHECK_PROGRESS_ENDPOINT = "/api/v1/BuyOnline/CheckProgress";
    const CHANGE_PASSWORD = "/api/v1/Others/ChangePasswordAgent";

    public static function getAccessToken()
{
    if (cache()->has('jaib_access_token') && cache()->has('jaib_token_expiry') && time() < cache()->get('jaib_token_expiry')) {
        return (object)[
            'access_token' => cache()->get('jaib_access_token'),
            'pinApi' => cache()->get('jaib_pin_api'),
        ];
    }

    $params = [
        'userName' => env('JAIB_USER'),
        'password' => env('JAIB_PASS'),
        'agentCode' => env('JAIB_AGENT_CODE'),
    ];
    $serviceURL = self::URL . self::LOGIN_ENDPOINT;

    $headers = [
        'Content-Type: application/json',
    ];

    $tokenResponse = self::invokeCurlRequest("POST", $serviceURL, $headers, json_encode($params));

    if ($tokenResponse === false) {
        throw new Exception('Failed to connect to Jaib API');
    }

    $tokenResponse = json_decode($tokenResponse);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from Jaib API');
    }

    if (!$tokenResponse->success) {
        throw new Exception('Failed to get access token: ' . $tokenResponse->error->message);
    }

    $accessToken = $tokenResponse->result->accessToken;
    $pinApi = $tokenResponse->result->pinApi;
    $expiryTime = time() + $tokenResponse->result->expire;

    cache()->put('jaib_access_token', $accessToken, $tokenResponse->result->expire);
    cache()->put('jaib_pin_api', $pinApi, $tokenResponse->result->expire);
    cache()->put('jaib_token_expiry', $expiryTime, $tokenResponse->result->expire);

    return (object)[
        'access_token' => $accessToken,
        'pinApi' => $pinApi,
    ];
}


    public static function make_payment($user_phone, $requestID, $code, $amount, $currency_code)
    {
        $login_data = self::getAccessToken();

        $params = [
            'pinApi' => $login_data->pinApi,
            'mobile' => $user_phone,
            'amount' => floatval($amount),
            'currencyCode' => $currency_code,
            'code' => $code,
            'requestID' => $requestID,
            'notes' => 'Payment for Samh code:'.$requestID,
        ];

        $requestHeaders = [
            "Authorization: Bearer " . $login_data->access_token,
            "Content-Type: application/json"
        ];

        $requestResponse = self::invokeCurlRequest("POST", self::URL . self::PAYMENT_ENDPOINT, $requestHeaders, json_encode($params));

        if ($requestResponse === false) {
            throw new Exception('Failed to connect to Jaib API');
        }

        $response = json_decode($requestResponse);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Jaib API');
        }

        return $response;
    }

    public static function refund($amount, $requestID, $referenceID, $currency_code)
    {
        $login_data = self::getAccessToken();


        $params = [
            'pinApi' => $login_data->pinApi,
            'amount' => $amount,
            'currencyCode' => $currency_code,
            'referenceID' => $referenceID,
            'requestID' => $requestID,
            'notes' => '',
        ];

        $requestHeaders = [
            "Authorization: Bearer " . $login_data->access_token,
            "Content-Type: application/json"
        ];

        $requestResponse = self::invokeCurlRequest("POST", self::URL . self::REFUND_ENDPOINT, $requestHeaders, json_encode($params));

        if ($requestResponse === false) {
            throw new Exception('Failed to connect to Jaib API');
        }

        $response = json_decode($requestResponse);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Jaib API');
        }

        return $response;
    }

    public static function check($requestID)
    {
        $login_data = self::getAccessToken();

        $params = [
            'pinApi' => $login_data->pinApi,
            'requestID' => $requestID,
        ];

        $requestHeaders = [
            "Authorization: Bearer " . $login_data->access_token,
            "Content-Type: application/json"
        ];

        $requestResponse = self::invokeCurlRequest("POST", self::URL . self::CHECK_PROGRESS_ENDPOINT, $requestHeaders, json_encode($params));

        if ($requestResponse === false) {
            throw new Exception('Failed to connect to Jaib API');
        }

        $response = json_decode($requestResponse);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Jaib API');
        }

        return $response;
    }

    public static function change_password($old_pass, $new_pass)
    {
        $login_data = self::getAccessToken();

        $params = [
            'currentPassword' => $old_pass,
            'newPassword' => $new_pass,
        ];

        $requestHeaders = [
            "Authorization: Bearer " . $login_data->access_token,
            "Content-Type: application/json"
        ];

        $requestResponse = self::invokeCurlRequest("POST", self::URL . self::CHANGE_PASSWORD, $requestHeaders, json_encode($params));

        if ($requestResponse === false) {
            throw new Exception('Failed to connect to Jaib API');
        }

        $response = json_decode($requestResponse);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Jaib API');
        }

        return $response;
    }
    public static function invokeCurlRequest($type, $url, $headers, $post)
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
            Log::error('Curl error: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return $server_output;
    }
}
