<?php

namespace App\Services;

use App\Helpers\noncestrHelper;
use App\Helpers\signatureHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PalmPayService
{
    protected $baseUrl;
    protected $bearerToken;
    protected $merchantId;

    public function __construct()
    {
        // Get credentials from config
        $baseUrl = config('services.palmpay.base_url', 'https://open-gw-prod.palmpay-inc.com/');
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->bearerToken = config('services.palmpay.bearer_token');
        $this->merchantId = config('services.palmpay.merchant_id');
    }

    /**
     * Get the list of banks from PalmPay.
     */
    public function queryBankList($businessType = 0)
    {
        $data = [
            'requestTime' => (int) (microtime(true) * 1000),
            'version' => config('services.palmpay.version', 'V2.0'),
            'nonceStr' => noncestrHelper::generateNonceStr(),
            'businessType' => $businessType,
        ];

        return $this->post('api/v2/general/merchant/queryBankList', $data);
    }

    /**
     * Query bank account name.
     */
    public function queryBankAccount($bankCode, $bankAccNo)
    {
        $data = [
            'requestTime' => (int) (microtime(true) * 1000),
            'version' => config('services.palmpay.version', 'V2.0'),
            'nonceStr' => noncestrHelper::generateNonceStr(),
            'bankCode' => $bankCode,
            'bankAccNo' => $bankAccNo,
        ];

        // Specific endpoint for PalmPay account query if bankCode is 100033 (V2)
        $endpoint = ($bankCode === '100033')
            ? 'api/v2/payment/merchant/payout/queryAccount'
            : 'api/v2/payment/merchant/payout/queryBankAccount';

        return $this->post($endpoint, $data);
    }

    /**
     * Payout / Transfer funds to a bank account.
     */
    public function transfer($params)
    {
        $data = array_merge([
            'requestTime' => (int) (microtime(true) * 1000),
            'version' => 'V1.1',
            'nonceStr' => noncestrHelper::generateNonceStr(),
        ], $params);

        return $this->post('api/v2/merchant/payment/payout', $data);
    }

    /**
     * Perform the actual POST request with PalmPay signatures.
     */
    protected function post($endpoint, $data)
    {
        $signature = signatureHelper::generate_signature($data, config('keys.private'));
        $url = $this->baseUrl . ltrim($endpoint, '/');

        Log::info("PalmPay Request to $url", ['data' => $data]);

        $token = $this->bearerToken;
        $headers = [
            'Accept: application/json, text/plain, */*',
            'CountryCode: NG',
            "Authorization: Bearer $token",
            "Signature: $signature",
            'Content-Type: application/json',
        ];

        // Initialize cURL
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Disable SSL verification in local environment
        if (config('app.env') === 'local') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        // Execute request
        $response = curl_exec($ch);

        Log::info("PalmPay Response from $url", ['response' => $response]);

        // Check for cURL errors
        if (curl_errno($ch)) {
            Log::error('cURL Error: ' . curl_error($ch));
            return ['respCode' => '9999', 'respMsg' => curl_error($ch)];
        }

        // Close cURL session
        curl_close($ch);

        return json_decode($response, true);
    }
}
