<?php
namespace App\Utility\PaymentUtility;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\ApiKeys;
use Carbon\Carbon;
use App\Models\FloosakWallet;

class FloosakUtility {

    const SERVICE_URL = "https://staging.fintech-expert.net";
    const GENERATE_KEY_ENDPOINT = "/api/v1/request/key";
    const VERIFY_KEY_ENDPOINT = "/api/v1/verify/key";
    const PURCHASE_ENDPOINT = "/api/v1/merchant/p2mcl";


    public function getKey(){
        if(env('FLOOSAK_SHORT_CODE') == '' ||  env('FLOOSAK_PHONE_NUMBER') == ''){
            throw new Exception(translate('short code and phone number can not be empty'));
        }
        $params = [
            'short_code' => env('FLOOSAK_SHORT_CODE'),
            'phone' =>env('FLOOSAK_PHONE_NUMBER')
        ];

        $serviceURL = self::SERVICE_URL . self::GENERATE_KEY_ENDPOINT;

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'x-channel: merchant'
        ];

        $response = self::invokeCurlRequest("POST", $serviceURL, $headers, json_encode($params));

        if ($response === false) {
            throw new Exception('Failed to connect to Floosak API');
        }

        $response = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Floosak API');
        }

        if (!isset($response->request_id)) {
            throw new Exception('Failed to get request id: ' . $response->message);
        }

      return $response->request_id;

    }


    public function verifyKey($request_id , $otp ){
        $params = [
            'request_id' => $request_id,
            'otp' => $otp
        ];

        $serviceURL = self::SERVICE_URL . self::VERIFY_KEY_ENDPOINT;

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'x-channel: merchant'
        ];

        $response = self::invokeCurlRequest("POST", $serviceURL, $headers, json_encode($params));

        if ($response === false) {
            throw new Exception('Failed to connect to Floosak API');
        }

        $response = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Floosak API');
        }

        if (!isset($response->key)) {
            throw new Exception('Failed to verify key: ' . $response->message);
        }

       return $response;

    }


    public function requestPurchase($sourceWalletId, $requestId, $targetPhone, $amount, $purpose=''){
        $apiKey = ApiKeys::where('service', 'floosak')->first();

        if(!$apiKey || $apiKey->expiry_date->isPast()){
            throw new Exception('Service is not available');
        }
        $token = config('wallet-payment.floosak.api_key');

        $params = [
            'source_wallet_id' => $sourceWalletId,
            'request_id' => $requestId,
            'target_phone' => $targetPhone,
            'amount' => $amount,
            'purpose' => $purpose,
        ];

        $serviceURL = self::SERVICE_URL . self::PURCHASE_ENDPOINT;

        $headers = [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
            'Content-Type: application/json',
            'x-channel: merchant',
        ];

        $response = self::invokeCurlRequest("POST", $serviceURL, $headers, json_encode($params));

        if ($response === false) {
            throw new Exception('Failed to connect to Floosak API');
        }

        $response = json_decode($response);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Floosak API');
        }

        if (!isset($response->is_success) || $response->is_success == false) {
            throw new Exception('Failed to request purchase: ' . $response->message);
        }

        return $response;

    }

    public function confirmPurchase($purchaseId, $otp){
        $apiKey = ApiKeys::where('service', 'floosak')->first();
        if(!$apiKey || $apiKey->expiry_date->isPast()){
            throw new Exception('Service is not available');
        }
        $token = config('wallet-payment.floosak.api_key');
        $params = [
            'otp' => $otp,
            'purchase_id' => $purchaseId,
        ];

        $serviceURL = self::SERVICE_URL . self::PURCHASE_ENDPOINT;

        $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'x-channel: merchant'
        ];

        $response = self::invokeCurlRequest("POST", $serviceURL, $headers, json_encode($params));

        if ($response === false) {
            throw new Exception('Failed to connect to Floosak API');
        }

        $response = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from Floosak API');
        }

        if (!isset($response->is_success) || $response->is_success == false) {
            throw new Exception('Failed to request purchase: ' . $response->message);
        }

        return $response;

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
