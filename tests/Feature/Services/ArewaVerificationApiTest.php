<?php

use App\Services\ArewaVerificationApi;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

it('returns the raw response on success so each controller can parse its own shape', function () {
    Http::fake(['*' => Http::response(['status' => 'success', 'data' => ['firstname' => 'Jane']], 200)]);

    $response = ArewaVerificationApi::verifyNin('12345678901', 'REQ300');

    expect($response->successful())->toBeTrue()
        ->and($response->json('data.firstname'))->toBe('Jane');
    Http::assertSentCount(1);
});

it('does not retry on a 500 response', function () {
    Http::fake(['*' => Http::response(['status' => 'error'], 500)]);

    $response = ArewaVerificationApi::verifyBvn('22212345678', 'REQ301');

    expect($response->successful())->toBeFalse();
    Http::assertSentCount(1);
});

it('recovers on the retry after one connection failure', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        if ($attempts === 1) {
            throw new ConnectionException('Timed out');
        }

        return Http::response(['status' => 'success'], 200);
    });

    $response = ArewaVerificationApi::verifyNinByPhone('08031234567', 'REQ302');

    expect($response->successful())->toBeTrue()
        ->and($attempts)->toBe(2);
});

it('gives up after the retry is also a connection failure', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        throw new ConnectionException('Timed out');
    });

    expect(fn () => ArewaVerificationApi::verifyNinByPhone('08031234567', 'REQ303'))
        ->toThrow(ConnectionException::class);

    expect($attempts)->toBe(2);
});

it('throws immediately without calling the vendor once the circuit is open', function () {
    Http::fake(['*' => Http::response(['status' => 'error'], 500)]);

    for ($i = 0; $i < 5; $i++) {
        ArewaVerificationApi::verifyNin('12345678901', "REQ40{$i}");
    }

    expect(fn () => ArewaVerificationApi::verifyNin('12345678901', 'REQ410'))
        ->toThrow(RuntimeException::class, 'temporarily unavailable');

    Http::assertSentCount(5);
});
