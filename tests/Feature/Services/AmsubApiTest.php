<?php

use App\Models\SmeDataProviderSetting;
use App\Services\AmsubApi;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

function fakeAmsubSettings(): SmeDataProviderSetting
{
    return new SmeDataProviderSetting(['api_key' => 'test-token-key']);
}

it('sends the correct request body and Token auth header', function () {
    Http::fake([
        '*' => Http::response(['status' => 'success', 'request-id' => 'Data_1234567890', 'message' => 'OK'], 200),
    ]);

    (new AmsubApi(fakeAmsubSettings()))->purchase('09061668519', 'MTN', '1', 'REQ1');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://amsubapi.com/api/data'
            && $request['network'] === 1
            && $request['data_plan'] === '1'
            && $request['phone'] === '09061668519'
            && $request['bypass'] === false
            && $request['request-id'] === 'REQ1'
            && $request->hasHeader('Authorization')
            && $request->header('Authorization')[0] === 'Token test-token-key';
    });
});

it('maps networks using AMSUB\'s own numbering', function () {
    Http::fake(['*' => Http::response(['status' => 'success', 'request-id' => 'REQ2'], 200)]);

    (new AmsubApi())->purchase('09061668519', 'GLO', '1', 'REQ2');

    Http::assertSent(fn ($request) => $request['network'] === 3);
});

it('parses a successful vendor response', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'request-id' => 'Data_1234567890',
            'amount' => '100',
            'dataplan' => '500MB',
            'message' => 'Yello! You have gifted 500MB.',
            'phone_number' => '07013397088',
        ], 200),
    ]);

    $result = (new AmsubApi())->purchase('09061668519', 'MTN', '1', 'REQ3');

    expect($result['success'])->toBeTrue()
        ->and($result['transaction_ref'])->toBe('Data_1234567890');

    Http::assertSentCount(1);
});

it('does not retry on a 500 response', function () {
    Http::fake(['*' => Http::response(['message' => 'Server error'], 500)]);

    $result = (new AmsubApi())->purchase('09061668519', 'MTN', '1', 'REQ4');

    expect($result['success'])->toBeFalse();
    Http::assertSentCount(1);
});

it('treats a non-"success" status as a failure', function () {
    Http::fake(['*' => Http::response(['status' => 'failed', 'message' => 'Insufficient balance'], 200)]);

    $result = (new AmsubApi())->purchase('09061668519', 'MTN', '1', 'REQ4b');

    expect($result['success'])->toBeFalse();
});

it('recovers on the retry after one connection failure', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        if ($attempts === 1) {
            throw new ConnectionException('Could not resolve host');
        }

        return Http::response(['status' => 'success', 'request-id' => 'REQ5'], 200);
    });

    $result = (new AmsubApi())->purchase('09061668519', 'MTN', '1', 'REQ5');

    expect($result['success'])->toBeTrue()
        ->and($attempts)->toBe(2);
});

it('opens the circuit after repeated failures and skips the call', function () {
    Http::fake(['*' => Http::response(['message' => 'Server error'], 500)]);

    for ($i = 0; $i < 5; $i++) {
        (new AmsubApi())->purchase('09061668519', 'MTN', '1', "REQ6{$i}");
    }

    Http::fake(['*' => Http::response(['status' => 'success'], 200)]);
    $result = (new AmsubApi())->purchase('09061668519', 'MTN', '1', 'REQ70');

    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('temporarily unavailable');
});
