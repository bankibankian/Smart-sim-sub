<?php

namespace App\Jobs;

use App\Models\Bank;
use App\Services\PalmPayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Admin-triggered PalmPay bank-list sync, moved off the request thread per
 * the outbound-API standard's "CRM/third-party state syncs must be queued"
 * rule — read-only and naturally idempotent (each run just upserts the
 * current list), so a plain background job with retries is all this needs.
 */
class SyncPalmPayBankListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function middleware(): array
    {
        return [new RateLimited('palmpay-vendor')];
    }

    public function handle(PalmPayService $palmPay): void
    {
        $response = $palmPay->queryBankList();

        if (!isset($response['respCode']) || $response['respCode'] !== '00000000') {
            Log::error('PalmPay bank list sync failed', ['response' => $response]);

            return;
        }

        foreach ($response['data'] as $bank) {
            Bank::updateOrCreate(
                ['bank_code' => $bank['bankCode']],
                [
                    'bank_name' => $bank['bankName'],
                    'bank_url'  => $bank['bankUrl'] ?? null,
                    'bg_url'    => $bank['bgUrl'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        Log::info('PalmPay bank list synced', ['count' => count($response['data'])]);
    }
}
