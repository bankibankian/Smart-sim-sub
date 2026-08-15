<?php

use App\Models\SmeDataProviderSetting;
use App\Services\NinePsbDataApi;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

function fakeNinePsbSettings(): SmeDataProviderSetting
{
    return new SmeDataProviderSetting([
        'provider' => '9psb',
        'base_url' => 'https://vas.example.com/api/v1',
        'auth_base_url' => 'https://auth.example.com/api/v1',
        'api_key' => 'test-api-key',
        'secret_key' => 'test-secret-key',
        'debit_account' => '10180006',
    ]);
}

function ninePsbAuthResponse(int $expiresInMs = 7200000): array
{
    return ['status' => 'success', 'data' => ['accessToken' => 'test-access-token', 'expiresIn' => $expiresInMs]];
}

it('authenticates then sends the correct topup request body and Bearer token', function () {
    Http::fake([
        '*/authenticate' => Http::response(ninePsbAuthResponse(), 200),
        '*/topup/data' => Http::response(['status' => 'success', 'data' => ['transactionReference' => 'REF1']], 200),
    ]);

    (new NinePsbDataApi(fakeNinePsbSettings()))->purchase('08134943416', 'MTN', 'MTN-200MB-2', 'REQ1', '200.00');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://auth.example.com/api/v1/authenticate'
            && $request['username'] === 'test-api-key'
            && $request['password'] === 'test-secret-key';
    });

    Http::assertSent(function ($request) {
        return $request->url() === 'https://vas.example.com/api/v1/topup/data'
            && $request['phoneNumber'] === '08134943416'
            && $request['amount'] === '200.00'
            && $request['debitAccount'] === '10180006'
            && $request['network'] === 'MTN'
            && $request['productId'] === 'MTN-200MB-2'
            && $request['transactionReference'] === 'REQ1'
            && $request->hasHeader('Authorization')
            && $request->header('Authorization')[0] === 'Bearer test-access-token';
    });
});

it('parses a successful vendor response', function () {
    Http::fake([
        '*/authenticate' => Http::response(ninePsbAuthResponse(), 200),
        '*/topup/data' => Http::response(['status' => 'success', 'data' => ['transactionReference' => 'REF2', 'recipient' => '08134943416']], 200),
    ]);

    $result = (new NinePsbDataApi(fakeNinePsbSettings()))->purchase('08134943416', 'MTN', 'MTN-200MB-2', 'REQ2', '200.00');

    expect($result['success'])->toBeTrue()
        ->and($result['transaction_ref'])->toBe('REF2');
});

it('reuses a cached access token instead of re-authenticating on a second purchase', function () {
    $authCalls = 0;
    Http::fake([
        '*/authenticate' => function () use (&$authCalls) {
            $authCalls++;
            return Http::response(ninePsbAuthResponse(), 200);
        },
        '*/topup/data' => Http::response(['status' => 'success', 'data' => ['transactionReference' => 'REF3']], 200),
    ]);

    $api = new NinePsbDataApi(fakeNinePsbSettings());
    $api->purchase('08134943416', 'MTN', 'MTN-200MB-2', 'REQ3A', '200.00');
    $api->purchase('08134943416', 'MTN', 'MTN-200MB-2', 'REQ3B', '200.00');

    expect($authCalls)->toBe(1);
    Http::assertSentCount(3); // 1 auth + 2 topups
});

it('short-circuits without calling topup/data when authentication fails', function () {
    Http::fake([
        '*/authenticate' => Http::response(['status' => 'error', 'message' => 'Invalid credentials'], 401),
    ]);

    $result = (new NinePsbDataApi(fakeNinePsbSettings()))->purchase('08134943416', 'MTN', 'MTN-200MB-2', 'REQ4', '200.00');

    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('authentication failed');

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/topup/data'));
});

it('does not retry on a 500 response from the topup call', function () {
    Http::fake([
        '*/authenticate' => Http::response(ninePsbAuthResponse(), 200),
        '*/topup/data' => Http::response(['message' => 'Server error'], 500),
    ]);

    $result = (new NinePsbDataApi(fakeNinePsbSettings()))->purchase('08134943416', 'MTN', 'MTN-200MB-2', 'REQ5', '200.00');

    expect($result['success'])->toBeFalse();
    Http::assertSentCount(2); // 1 auth + 1 topup, no retry on non-connection failure
});

it('recovers on the retry after one connection failure on the topup call', function () {
    $attempts = 0;
    Http::fake([
        '*/authenticate' => Http::response(ninePsbAuthResponse(), 200),
        '*/topup/data' => function () use (&$attempts) {
            $attempts++;
            if ($attempts === 1) {
                throw new ConnectionException('Could not resolve host');
            }

            return Http::response(['status' => 'success', 'data' => ['transactionReference' => 'REF6']], 200);
        },
    ]);

    $result = (new NinePsbDataApi(fakeNinePsbSettings()))->purchase('08134943416', 'MTN', 'MTN-200MB-2', 'REQ6', '200.00');

    expect($result['success'])->toBeTrue()
        ->and($attempts)->toBe(2);
});

it('opens the circuit after repeated topup failures and skips the call', function () {
    Http::fake([
        '*/authenticate' => Http::response(ninePsbAuthResponse(), 200),
        '*/topup/data' => Http::response(['message' => 'Server error'], 500),
    ]);

    $settings = fakeNinePsbSettings();
    for ($i = 0; $i < 5; $i++) {
        (new NinePsbDataApi($settings))->purchase('08134943416', 'MTN', 'MTN-200MB-2', "REQ7{$i}", '200.00');
    }

    Http::fake(['*' => Http::response(['status' => 'success'], 200)]);
    $result = (new NinePsbDataApi($settings))->purchase('08134943416', 'MTN', 'MTN-200MB-2', 'REQ70', '200.00');

    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('temporarily unavailable');
});
