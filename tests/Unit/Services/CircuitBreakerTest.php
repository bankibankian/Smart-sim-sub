<?php

use App\Services\CircuitBreaker;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('starts closed', function () {
    $breaker = new CircuitBreaker('test_vendor');

    expect($breaker->isOpen())->toBeFalse();
});

it('opens after the failure threshold is reached', function () {
    $breaker = new CircuitBreaker('test_vendor', failureThreshold: 3);

    $breaker->recordFailure();
    $breaker->recordFailure();
    expect($breaker->isOpen())->toBeFalse();

    $breaker->recordFailure();
    expect($breaker->isOpen())->toBeTrue();
});

it('resets the failure count on success', function () {
    $breaker = new CircuitBreaker('test_vendor', failureThreshold: 3);

    $breaker->recordFailure();
    $breaker->recordFailure();
    $breaker->recordSuccess();
    $breaker->recordFailure();

    expect($breaker->isOpen())->toBeFalse();
});

it('keeps separate state per vendor', function () {
    $a = new CircuitBreaker('vendor_a', failureThreshold: 1);
    $b = new CircuitBreaker('vendor_b', failureThreshold: 1);

    $a->recordFailure();

    expect($a->isOpen())->toBeTrue()
        ->and($b->isOpen())->toBeFalse();
});
