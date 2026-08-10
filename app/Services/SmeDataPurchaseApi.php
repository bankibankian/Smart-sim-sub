<?php

namespace App\Services;

use App\Support\LogRedactor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The Fadeelposdatasub data-purchase vendor call, extracted out of
 * SmeDataController so it can be reused for the silent post-activation
 * bonus top-up without duplicating the integration.
 */
class SmeDataPurchaseApi
{
    private const NETWORK_ID_MAP = [
        'MTN' => 1,
        'GLO' => 2,
        'AIRTEL' => 3,
        '9MOBILE' => 4,
    ];

    /**
     * More generous than a typical REST API's timeout — this vendor is
     * empirically slow but usually still successful, and a tight timeout
     * would trigger spurious refunds on legitimate-but-slow responses.
     */
    private const CONNECT_TIMEOUT = 3;
    private const TIMEOUT = 20;

    public static function baseUrl(): string
    {
        return env('BASE_URL', 'https://fadeelposdatasub.com.ng/api/data/purchase');
    }

    public static function token(): ?string
    {
        return env('API_KEYS');
    }

    /**
     * Calls the vendor's data-purchase endpoint for a mobile number.
     *
     * @return array{success: bool, data: array, api_data: array, transaction_ref: string, message: ?string}
     */
    public static function purchase(string $mobile, string $network, string $planId, string $requestId): array
    {
        $networkId = self::NETWORK_ID_MAP[strtoupper($network)] ?? 1;
        $payload = [
            'mobile_number' => $mobile,
            'network'       => $networkId,
            'plan'          => $planId,
            'request-id'    => $requestId,
        ];

        $breaker = new CircuitBreaker('sme_data');

        if ($breaker->isOpen()) {
            Log::warning('SME Data vendor circuit is open — skipping call', ['request_id' => $requestId]);

            return [
                'success'         => false,
                'data'            => [],
                'api_data'        => [],
                'transaction_ref' => $requestId,
                'message'         => 'Data vendor is temporarily unavailable. Please try again shortly.',
            ];
        }

        Log::info('SME Data API Request Payload', [
            'url'     => self::baseUrl(),
            'payload' => LogRedactor::redact($payload),
        ]);

        try {
            $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
                ->timeout(self::TIMEOUT)
                ->retry(times: 2, sleepMilliseconds: 300, when: fn ($e) => $e instanceof ConnectionException, throw: false)
                ->withHeaders([
                    'Authorization' => 'Token ' . self::token(),
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])->withoutVerifying()->post(self::baseUrl(), $payload);
        } catch (ConnectionException $e) {
            $breaker->recordFailure();
            throw $e;
        }

        $data = $response->json();
        Log::info('SME Data API Response', [
            'status'   => $response->status(),
            'response' => LogRedactor::redact((array) $data),
            'raw_body' => $response->body(),
        ]);

        $isSuccess = $response->successful() && (
            (isset($data['status']) && ($data['status'] === 'success' || $data['status'] === 'successful')) ||
            (isset($data['success']) && $data['success'] === true) ||
            (isset($data['code']) && in_array((string) $data['code'], ['200', '201', '0', '00', '000'], true))
        );

        $isSuccess ? $breaker->recordSuccess() : $breaker->recordFailure();

        $apiData = $data['data'] ?? [];
        $transactionRef = $data['transid'] ?? $data['reference'] ?? $data['transaction_id'] ?? $apiData['transaction_ref'] ?? $apiData['reference'] ?? $requestId;

        return [
            'success'         => $isSuccess,
            'data'            => $data,
            'api_data'        => $apiData,
            'transaction_ref' => $transactionRef,
            'message'         => $data['message'] ?? $data['msg'] ?? null,
        ];
    }
}
