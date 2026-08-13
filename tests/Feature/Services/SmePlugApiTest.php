<?php

use App\Models\SmeDataProviderSetting;
use App\Services\SmePlugApi;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

function fakeSmePlugSettings(): SmeDataProviderSetting
{
    return new SmeDataProviderSetting(['api_key' => 'test-bearer-token']);
}

it('sends the correct request body and Bearer auth header', function () {
    Http::fake([
        '*' => Http::response(['status' => true, 'data' => ['reference' => 'REF1', 'msg' => 'OK']], 200),
    ]);

    (new SmePlugApi(fakeSmePlugSettings()))->purchase('09061668519', 'MTN', '1', 'REQ1');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://smeplug.ng/api/v1/data/purchase'
            && $request['network_id'] === 1
            && $request['plan_id'] === '1'
            && $request['phone'] === '09061668519'
            && $request->hasHeader('Authorization')
            && $request->header('Authorization')[0] === 'Bearer test-bearer-token';
    });
});

it('maps networks using SME Plug\'s own numbering, not the legacy vendor\'s', function () {
    Http::fake(['*' => Http::response(['status' => true, 'data' => []], 200)]);

    (new SmePlugApi())->purchase('09061668519', 'AIRTEL', '1', 'REQ2');

    Http::assertSent(fn ($request) => $request['network_id'] === 2);
});

it('parses a successful vendor response', function () {
    Http::fake([
        '*' => Http::response(['status' => true, 'data' => ['reference' => '49e2e6ea701054ff164c', 'msg' => 'You have successfully transferred 500MB Data to 2349061668519.']], 200),
    ]);

    $result = (new SmePlugApi())->purchase('09061668519', 'MTN', '1', 'REQ3');

    expect($result['success'])->toBeTrue()
        ->and($result['transaction_ref'])->toBe('49e2e6ea701054ff164c');

    Http::assertSentCount(1);
});

it('does not retry on a 500 response', function () {
    Http::fake(['*' => Http::response(['message' => 'Server error'], 500)]);

    $result = (new SmePlugApi())->purchase('09061668519', 'MTN', '1', 'REQ4');

    expect($result['success'])->toBeFalse();
    Http::assertSentCount(1);
});

it('recovers on the retry after one connection failure', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        if ($attempts === 1) {
            throw new ConnectionException('Could not resolve host');
        }

        return Http::response(['status' => true, 'data' => ['reference' => 'REF5']], 200);
    });

    $result = (new SmePlugApi())->purchase('09061668519', 'MTN', '1', 'REQ5');

    expect($result['success'])->toBeTrue()
        ->and($attempts)->toBe(2);
});

it('opens the circuit after repeated failures and skips the call', function () {
    Http::fake(['*' => Http::response(['message' => 'Server error'], 500)]);

    for ($i = 0; $i < 5; $i++) {
        (new SmePlugApi())->purchase('09061668519', 'MTN', '1', "REQ6{$i}");
    }

    Http::fake(['*' => Http::response(['status' => true], 200)]);
    $result = (new SmePlugApi())->purchase('09061668519', 'MTN', '1', 'REQ70');

    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('temporarily unavailable');
});
