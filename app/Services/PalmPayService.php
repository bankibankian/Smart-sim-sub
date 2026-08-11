<?php

namespace App\Services;

use App\Helpers\noncestrHelper;
use App\Helpers\signatureHelper;
use App\Support\LogRedactor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PalmPayService
{
    private const CONNECT_TIMEOUT = 3;
    private const TIMEOUT = 20;

    protected $baseUrl;
    protected $bearerToken;
    protected $merchantId;

    public function __construct()
    {
        // Read via config(), not env() directly — env() outside a config
        // file silently returns null once config is cached (routine in
        // staging/production), which made this class blind to real
        // credentials while config()-based callers kept working fine.
        $baseUrl = config('services.palmpay.base_url');
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->bearerToken = config('services.palmpay.bearer_token');
        $this->merchantId = config('services.palmpay.merchant_id');
    }

    /**
     * Get the list of banks from PalmPay.
     */
    public function queryBankList($businessType = 0)
    {
        $version = config('services.palmpay.version', 'V2.0');
        $data = [
            'requestTime' => (int) (microtime(true) * 1000),
            'version' => $version,
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
        $version = config('services.palmpay.version', 'V2.0');
        $data = [
            'requestTime' => (int) (microtime(true) * 1000),
            'version' => $version,
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

        Log::info("PalmPay Request to $url", ['data' => LogRedactor::redact($data)]);

        $breaker = new CircuitBreaker('palmpay');

        if ($breaker->isOpen()) {
            Log::warning("PalmPay circuit is open — skipping request to $url");

            return ['respCode' => '9999', 'respMsg' => 'PalmPay is temporarily unavailable. Please try again shortly.'];
        }

        $token = $this->bearerToken;
        $headers = [
            'Accept'        => 'application/json, text/plain, */*',
            'CountryCode'   => 'NG',
            'Authorization' => "Bearer $token",
            'Signature'     => $signature,
            'Content-Type'  => 'application/json',
        ];

        try {
            $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
                ->timeout(self::TIMEOUT)
                ->retry(times: 2, sleepMilliseconds: 300, when: fn ($e) => $e instanceof ConnectionException, throw: false)
                ->withHeaders($headers)
                ->when(config('app.env') === 'local', fn ($h) => $h->withoutVerifying())
                ->post($url, $data);
        } catch (ConnectionException $e) {
            // Deliberately re-thrown rather than swallowed into a normal
            // ['respCode' => '9999', ...] return: a connection failure is
            // genuinely ambiguous (the request may still have reached
            // PalmPay), so callers must treat it differently from an
            // explicit vendor rejection — this is what makes
            // WithdrawController's "leave pending, don't auto-refund on
            // ambiguous failure" logic around transfer() actually correct.
            $breaker->recordFailure();
            Log::error('PalmPay connection error: ' . $e->getMessage());

            throw $e;
        }

        Log::info("PalmPay Response from $url", ['response' => LogRedactor::redact((array) $response->json())]);

        $decoded = $response->json();
        $isSuccess = $response->successful() && is_array($decoded) && ($decoded['respCode'] ?? null) === '00000000';
        $isSuccess ? $breaker->recordSuccess() : $breaker->recordFailure();

        return $decoded;
    }
}
