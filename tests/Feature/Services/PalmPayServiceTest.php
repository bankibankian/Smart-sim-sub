<?php

use App\Services\PalmPayService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

it('parses a successful vendor response', function () {
    Http::fake(['*' => Http::response(['respCode' => '00000000', 'data' => []], 200)]);

    $response = (new PalmPayService())->queryBankList();

    expect($response['respCode'])->toBe('00000000');
    Http::assertSentCount(1);
});

it('does not retry on a 500 response', function () {
    Http::fake(['*' => Http::response(['respMsg' => 'Server error'], 500)]);

    $response = (new PalmPayService())->queryBankAccount('100033', '0123456789');

    expect($response['respCode'] ?? null)->not->toBe('00000000');
    Http::assertSentCount(1);
});

it('recovers on the retry after one connection failure', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;
        if ($attempts === 1) {
            throw new ConnectionException('Timed out');
        }

        return Http::response(['respCode' => '00000000', 'data' => []], 200);
    });

    $response = (new PalmPayService())->queryBankList();

    expect($response['respCode'])->toBe('00000000')
        ->and($attempts)->toBe(2);
});

it('re-throws (does not swallow) a connection failure that survives the retry, so the payout stays pending instead of being auto-refunded', function () {
    Http::fake(function () {
        throw new ConnectionException('Timed out');
    });

    expect(fn () => (new PalmPayService())->transfer(['orderId' => 'REF500', 'amount' => 100000]))
        ->toThrow(ConnectionException::class);
});
