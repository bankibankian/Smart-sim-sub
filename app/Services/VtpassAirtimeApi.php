<?php

namespace App\Services;

use App\Support\LogRedactor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The VTpass airtime-purchase vendor call, extracted out of
 * AirtimeController so the resilience patterns (timeout, retry, circuit
 * breaker, redacted logging) live in one place instead of inline.
 */
class VtpassAirtimeApi
{
    private const CONNECT_TIMEOUT = 3;
    private const TIMEOUT = 20;

    /**
     * Calls the vendor's airtime top-up endpoint for a mobile number.
     *
     * @return array{success: bool, data: array, message: ?string}
     */
    public static function purchase(string $mobile, string $networkKey, float $amount, string $requestId): array
    {
        $payload = [
            'request_id' => $requestId,
            'serviceID'  => $networkKey,
            'amount'     => $amount,
            'phone'      => $mobile,
        ];

        $breaker = new CircuitBreaker('vtpass_airtime');

        if ($breaker->isOpen()) {
            Log::warning('VTpass vendor circuit is open — skipping call', ['request_id' => $requestId]);

            return [
                'success' => false,
                'data'    => [],
                'message' => 'Airtime vendor is temporarily unavailable. Please try again shortly.',
            ];
        }

        Log::info('Airtime API Request Payload', [
            'url'     => config('services.vtpass.payment_url'),
            'payload' => LogRedactor::redact($payload),
        ]);

        try {
            $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
                ->timeout(self::TIMEOUT)
                ->retry(times: 2, sleepMilliseconds: 300, when: fn ($e) => $e instanceof ConnectionException, throw: false)
                ->withHeaders([
                    'api-key'    => config('services.vtpass.api_key'),
                    'secret-key' => config('services.vtpass.secret_key'),
                ])
                ->when(app()->environment() !== 'production', fn ($h) => $h->withoutVerifying())
                ->post(config('services.vtpass.payment_url'), $payload);
        } catch (ConnectionException $e) {
            $breaker->recordFailure();
            throw $e;
        }

        $data = $response->json();
        Log::info('Airtime API Response', [
            'status'   => $response->status(),
            'response' => LogRedactor::redact((array) $data),
        ]);

        $successCodes = ['0', '00', '000', '200'];
        $isSuccessful = false;

        if ($response->successful()) {
            if (isset($data['code']) && in_array((string) $data['code'], $successCodes, true)) {
                $isSuccessful = true;
            } elseif (isset($data['status']) && strtolower($data['status']) === 'success') {
                $isSuccessful = true;
            }
        }

        $isSuccessful ? $breaker->recordSuccess() : $breaker->recordFailure();

        return [
            'success' => $isSuccessful,
            'data'    => $data,
            'message' => $data['message'] ?? null,
        ];
    }
}
