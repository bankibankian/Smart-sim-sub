<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Per-vendor circuit breaker: after enough consecutive failures, trips open
 * for a cooldown window so callers can fail fast instead of waiting out a
 * full timeout on every request while the vendor is down. Backed by the
 * app's configured cache store (works fine on the 'database' driver already
 * in use here — no Redis dependency required).
 */
class CircuitBreaker
{
    public function __construct(
        private string $vendor,
        private int $failureThreshold = 5,
        private int $openMinutes = 5,
    ) {
    }

    public function isOpen(): bool
    {
        return Cache::has($this->downKey());
    }

    public function recordSuccess(): void
    {
        Cache::forget($this->failuresKey());
    }

    public function recordFailure(): void
    {
        // Cache::increment() on the 'database' cache driver returns false
        // (and does NOT initialize the key) when the key doesn't exist yet —
        // Cache::add() guarantees the key is present first, on every driver.
        Cache::add($this->failuresKey(), 0, now()->addMinutes($this->openMinutes));
        $failures = Cache::increment($this->failuresKey());

        if ($failures >= $this->failureThreshold) {
            Cache::put($this->downKey(), true, now()->addMinutes($this->openMinutes));
            Cache::forget($this->failuresKey());
            Log::alert("Circuit breaker tripped for {$this->vendor}. Blocking requests for {$this->openMinutes} minute(s).");
        }
    }

    private function downKey(): string
    {
        return "circuit:{$this->vendor}:down";
    }

    private function failuresKey(): string
    {
        return "circuit:{$this->vendor}:failures";
    }
}
