<?php

use App\Services\VtpassAirtimeApi;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

it('parses a successful vendor response', function () {
    Http::fake(['*' => Http::response(['code' => '000', 'message' => 'Successful'], 200)]);

    $result = VtpassAirtimeApi::purchase('08031234567', 'mtn', 500, 'REQ200');

    expect($result['success'])->toBeTrue();
    Http::assertSentCount(1);
});

it('does not retry on a 500 response', function () {
    Http::fake(['*' => Http::response(['message' => 'Server error'], 500)]);

    $result = VtpassAirtimeApi::purchase('08031234567', 'mtn', 500, 'REQ201');

    expect($result['success'])->toBeFalse();
    Http::assertSentCount(1);
});

it('recovers on the retry after one connection failure', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        if ($attempts === 1) {
            throw new ConnectionException('Timed out');
        }

        return Http::response(['code' => '000'], 200);
    });

    $result = VtpassAirtimeApi::purchase('08031234567', 'mtn', 500, 'REQ202');

    expect($result['success'])->toBeTrue()
        ->and($attempts)->toBe(2);
});

it('gives up after the retry is also a connection failure', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        throw new ConnectionException('Timed out');
    });

    expect(fn () => VtpassAirtimeApi::purchase('08031234567', 'mtn', 500, 'REQ203'))
        ->toThrow(ConnectionException::class);

    expect($attempts)->toBe(2);
});
