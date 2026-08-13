<?php

use App\Services\SmeDataPurchaseApi;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

it('parses a successful vendor response', function () {
    Http::fake([
        '*' => Http::response(['status' => 'success', 'transid' => 'TXN123', 'message' => 'OK'], 200),
    ]);

    $result = (new SmeDataPurchaseApi())->purchase('08031234567', 'MTN', 'PLAN1', 'REQ123');

    expect($result['success'])->toBeTrue()
        ->and($result['transaction_ref'])->toBe('TXN123');

    Http::assertSentCount(1);
});

it('does not retry on a 500 response', function () {
    Http::fake(['*' => Http::response(['message' => 'Server error'], 500)]);

    $result = (new SmeDataPurchaseApi())->purchase('08031234567', 'MTN', 'PLAN1', 'REQ124');

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

        return Http::response(['status' => 'success', 'transid' => 'TXN999'], 200);
    });

    $result = (new SmeDataPurchaseApi())->purchase('08031234567', 'MTN', 'PLAN1', 'REQ125');

    expect($result['success'])->toBeTrue()
        ->and($attempts)->toBe(2);
});

it('gives up after the retry is also a connection failure', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        throw new ConnectionException('Could not resolve host');
    });

    expect(fn () => (new SmeDataPurchaseApi())->purchase('08031234567', 'MTN', 'PLAN1', 'REQ126'))
        ->toThrow(ConnectionException::class);

    expect($attempts)->toBe(2);
});

it('opens the circuit after repeated failures and skips the call', function () {
    Http::fake(['*' => Http::response(['message' => 'Server error'], 500)]);

    for ($i = 0; $i < 5; $i++) {
        (new SmeDataPurchaseApi())->purchase('08031234567', 'MTN', 'PLAN1', "REQ12{$i}");
    }

    Http::fake(['*' => Http::response(['status' => 'success'], 200)]);
    $result = (new SmeDataPurchaseApi())->purchase('08031234567', 'MTN', 'PLAN1', 'REQ130');

    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('temporarily unavailable');
});
