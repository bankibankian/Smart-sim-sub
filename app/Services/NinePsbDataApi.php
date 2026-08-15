<?php

namespace App\Services;

use App\Contracts\SmeDataProviderInterface;
use App\Models\SmeDataProviderSetting;
use App\Support\LogRedactor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The 9PSB VAS data-topup vendor call — a third vendor behind
 * SmeDataProviderInterface. Unlike the other two vendors, 9PSB logs in with
 * a plain username/password (not an API key/secret pair) to a separate auth
 * endpoint; the resulting access token is cached until near expiry, then
 * sent as a bearer token on the actual topup call.
 */
class NinePsbDataApi implements SmeDataProviderInterface
{
    private const CONNECT_TIMEOUT = 3;
    private const TIMEOUT = 20;

    public function __construct(private ?SmeDataProviderSetting $settings = null)
    {
    }

    public function authBaseUrl(): string
    {
        return $this->settings?->auth_base_url ?: config('services.ninepsb.auth_base_url');
    }

    public function vasBaseUrl(): string
    {
        return $this->settings?->base_url ?: config('services.ninepsb.base_url');
    }

    public function username(): ?string
    {
        return $this->settings?->api_key ?: config('services.ninepsb.api_key');
    }

    public function password(): ?string
    {
        return $this->settings?->secret_key ?: config('services.ninepsb.secret_key');
    }

    public function debitAccount(): ?string
    {
        return $this->settings?->debit_account ?: config('services.ninepsb.debit_account');
    }

    /**
     * @return array{success: bool, data: array, api_data: array, transaction_ref: string, message: ?string}
     */
    public function purchase(string $mobile, string $network, string $planId, string $requestId, ?string $vendorAmount = null): array
    {
        $breaker = new CircuitBreaker('nine_psb');

        if ($breaker->isOpen()) {
            Log::warning('9PSB vendor circuit is open — skipping call', ['request_id' => $requestId]);

            return $this->failure($requestId, 'Data vendor is temporarily unavailable. Please try again shortly.');
        }

        $token = $this->authenticate();

        if ($token === null) {
            $breaker->recordFailure();

            return $this->failure($requestId, 'Data vendor authentication failed. Please try again shortly.');
        }

        $payload = [
            'phoneNumber'           => $mobile,
            'amount'                => (string) $vendorAmount,
            'debitAccount'          => $this->debitAccount(),
            'network'               => strtoupper($network),
            'productId'             => $planId,
            'transactionReference'  => $requestId,
        ];

        $url = rtrim($this->vasBaseUrl(), '/') . '/topup/data';

        Log::info('9PSB API Request Payload', [
            'url'     => $url,
            'payload' => LogRedactor::redact($payload),
        ]);

        try {
            $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
                ->timeout(self::TIMEOUT)
                ->retry(times: 2, sleepMilliseconds: 300, when: fn ($e) => $e instanceof ConnectionException, throw: false)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])->post($url, $payload);
        } catch (ConnectionException $e) {
            $breaker->recordFailure();
            throw $e;
        }

        $data = $response->json();
        Log::info('9PSB API Response', [
            'status'   => $response->status(),
            'response' => LogRedactor::redact((array) $data),
            'raw_body' => $response->body(),
        ]);

        $isSuccess = $response->successful() && is_array($data) && ($data['status'] ?? null) === 'success';
        $isSuccess ? $breaker->recordSuccess() : $breaker->recordFailure();

        $apiData = $data['data'] ?? [];

        return [
            'success'         => $isSuccess,
            'data'            => $data,
            'api_data'        => $apiData,
            'transaction_ref' => $apiData['transactionReference'] ?? $requestId,
            'message'         => $data['message'] ?? null,
        ];
    }

    /**
     * Fetches and caches the access token, keyed per settings row so
     * different vendor rows (e.g. sandbox vs. live) never share a token.
     * Returns null (not an exception) on auth failure — a distinct
     * "vendor auth failed" outcome from a connection failure.
     */
    private function authenticate(): ?string
    {
        $cacheKey = 'nine_psb:token:' . ($this->settings?->id ?? 'default');

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $url = rtrim($this->authBaseUrl(), '/') . '/authenticate';

        try {
            $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
                ->timeout(self::TIMEOUT)
                ->retry(times: 2, sleepMilliseconds: 300, when: fn ($e) => $e instanceof ConnectionException, throw: false)
                ->withHeaders(['Accept' => 'application/json'])
                ->post($url, [
                    'username' => $this->username(),
                    'password' => $this->password(),
                ]);
        } catch (ConnectionException $e) {
            return null;
        }

        $data = $response->json();

        if (!$response->successful() || !is_array($data) || empty($data['data']['accessToken'])) {
            Log::warning('9PSB authentication failed', [
                'status'   => $response->status(),
                'response' => LogRedactor::redact((array) $data),
            ]);

            return null;
        }

        // Cache under the token's own TTL (with a safety buffer) so it's
        // reused across purchases instead of re-authenticating every time.
        $ttlSeconds = max(60, (int) (($data['data']['expiresIn'] ?? 3600000) / 1000) - 60);
        Cache::put($cacheKey, $data['data']['accessToken'], now()->addSeconds($ttlSeconds));

        return $data['data']['accessToken'];
    }

    private function failure(string $requestId, string $message): array
    {
        return [
            'success'         => false,
            'data'            => [],
            'api_data'        => [],
            'transaction_ref' => $requestId,
            'message'         => $message,
        ];
    }
}
