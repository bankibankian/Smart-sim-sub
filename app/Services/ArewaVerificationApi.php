<?php

namespace App\Services;

use App\Support\LogRedactor;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The Arewa verification vendor calls (NIN, BVN, NIN-by-demographics,
 * NIN-by-phone), extracted out of four near-identical controllers so the
 * resilience patterns live in one place. Each verify* method returns the
 * raw Illuminate Response so each controller's own response-shape parsing
 * (they differ per endpoint — string vs boolean "status", nested
 * api_response, etc.) and refund logic stay exactly as they are today.
 */
class ArewaVerificationApi
{
    private const CONNECT_TIMEOUT = 3;
    private const TIMEOUT = 20;

    public static function verifyNin(string $nin, string $requestId): Response
    {
        return self::call('/nin/verify', ['nin' => $nin, 'ref' => $requestId], $requestId);
    }

    public static function verifyBvn(string $bvn, string $requestId): Response
    {
        return self::call('/bvn/verify', ['bvn' => $bvn, 'ref' => $requestId], $requestId);
    }

    public static function verifyNinDemo(string $firstName, string $lastName, string $gender, string $dateOfBirth, string $requestId): Response
    {
        return self::call('/nin/demo', [
            'firstName'   => $firstName,
            'lastName'    => $lastName,
            'gender'      => $gender,
            'dateOfBirth' => $dateOfBirth,
            'ref'         => $requestId,
        ], $requestId);
    }

    public static function verifyNinByPhone(string $phone, string $requestId): Response
    {
        return self::call('/nin/phone', ['value' => $phone, 'ref' => $requestId], $requestId);
    }

    private static function call(string $path, array $payload, string $requestId): Response
    {
        $breaker = new CircuitBreaker('arewa_verification');

        if ($breaker->isOpen()) {
            Log::warning('Arewa vendor circuit is open — skipping call', ['request_id' => $requestId, 'endpoint' => $path]);

            throw new \RuntimeException('Verification vendor is temporarily unavailable. Please try again shortly.');
        }

        // config(), not env() directly — env() outside a config file
        // silently returns null once config is cached.
        $baseUrl = config('services.arewa.base_url');
        $url = rtrim((string) $baseUrl, '/') . $path;

        Log::info('Arewa Verification API Request', [
            'url'     => $url,
            'payload' => LogRedactor::redact($payload),
        ]);

        try {
            $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
                ->timeout(self::TIMEOUT)
                ->retry(times: 2, sleepMilliseconds: 300, when: fn ($e) => $e instanceof ConnectionException, throw: false)
                ->withoutVerifying()
                ->withToken(config('services.arewa.api_token'))
                ->acceptJson()
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            $breaker->recordFailure();
            throw $e;
        }

        Log::info('Arewa Verification API Response', [
            'endpoint' => $path,
            'status'   => $response->status(),
            'response' => LogRedactor::redact((array) $response->json()),
        ]);

        $response->successful() ? $breaker->recordSuccess() : $breaker->recordFailure();

        return $response;
    }
}
