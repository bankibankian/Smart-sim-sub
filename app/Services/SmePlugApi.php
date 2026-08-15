<?php

namespace App\Services;

use App\Contracts\SmeDataProviderInterface;
use App\Models\SmeDataProviderSetting;
use App\Support\LogRedactor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The SME Plug (smeplug.ng) data-purchase vendor call — a second vendor
 * behind SmeDataProviderInterface, alongside SmeDataPurchaseApi.
 */
class SmePlugApi implements SmeDataProviderInterface
{
    // SME Plug's own network numbering (confirmed via their GET /networks
    // endpoint) — deliberately NOT shared with SmeDataPurchaseApi's map,
    // the two vendors number networks differently.
    private const NETWORK_ID_MAP = [
        'MTN' => 1,
        'AIRTEL' => 2,
        '9MOBILE' => 3,
        'GLO' => 4,
    ];

    private const CONNECT_TIMEOUT = 3;
    private const TIMEOUT = 20;

    public function __construct(private ?SmeDataProviderSetting $settings = null)
    {
    }

    public function baseUrl(): string
    {
        return $this->settings?->base_url ?: config('services.smeplug.base_url');
    }

    public function token(): ?string
    {
        return $this->settings?->api_key ?: config('services.smeplug.api_key');
    }

    /**
     * @return array{success: bool, data: array, api_data: array, transaction_ref: string, message: ?string}
     */
    public function purchase(string $mobile, string $network, string $planId, string $requestId, ?string $vendorAmount = null): array
    {
        $networkId = self::NETWORK_ID_MAP[strtoupper($network)] ?? 1;
        $payload = [
            'network_id' => $networkId,
            'plan_id'    => $planId,
            'phone'      => $mobile,
        ];

        $breaker = new CircuitBreaker('sme_plug');

        if ($breaker->isOpen()) {
            Log::warning('SME Plug vendor circuit is open — skipping call', ['request_id' => $requestId]);

            return [
                'success'         => false,
                'data'            => [],
                'api_data'        => [],
                'transaction_ref' => $requestId,
                'message'         => 'Data vendor is temporarily unavailable. Please try again shortly.',
            ];
        }

        $url = rtrim($this->baseUrl(), '/') . '/data/purchase';

        Log::info('SME Plug API Request Payload', [
            'url'     => $url,
            'payload' => LogRedactor::redact($payload),
        ]);

        try {
            $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
                ->timeout(self::TIMEOUT)
                ->retry(times: 2, sleepMilliseconds: 300, when: fn ($e) => $e instanceof ConnectionException, throw: false)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->token(),
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])->post($url, $payload);
        } catch (ConnectionException $e) {
            $breaker->recordFailure();
            throw $e;
        }

        $data = $response->json();
        Log::info('SME Plug API Response', [
            'status'   => $response->status(),
            'response' => LogRedactor::redact((array) $data),
            'raw_body' => $response->body(),
        ]);

        $isSuccess = $response->successful() && is_array($data) && ($data['status'] ?? false) === true;
        $isSuccess ? $breaker->recordSuccess() : $breaker->recordFailure();

        $apiData = $data['data'] ?? [];

        return [
            'success'         => $isSuccess,
            'data'            => $data,
            'api_data'        => $apiData,
            'transaction_ref' => $apiData['reference'] ?? $requestId,
            'message'         => $apiData['msg'] ?? $data['message'] ?? null,
        ];
    }
}
