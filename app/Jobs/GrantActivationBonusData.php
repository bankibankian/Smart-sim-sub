<?php

namespace App\Jobs;

use App\Helpers\RequestIdHelper;
use App\Models\ActivationBonusSettings;
use App\Models\Report;
use App\Models\Sim;
use App\Services\SmeDataPurchaseApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Silent post-activation bonus: tops up the just-activated SIM's own number
 * with a free data bundle, using the same vendor call as a normal "Buy
 * Data" purchase (SmeDataPurchaseApi) — no wallet is debited for it. Runs
 * in the background (queued) so it never delays the activation response.
 */
class GrantActivationBonusData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Background profile — patient, spread out, since nothing user-facing is waiting on this. */
    public int $tries = 4;
    public array $backoff = [30, 120, 600];

    public function __construct(public Sim $sim)
    {
    }

    public function middleware(): array
    {
        return [new RateLimited('sme-data-vendor')];
    }

    public function handle(): void
    {
        $settings = ActivationBonusSettings::forProvider($this->sim->provider);
        if (!$settings->is_active || !$settings->plan) {
            return;
        }

        $plan = $settings->plan;
        $requestId = RequestIdHelper::generateRequestId();

        // A ConnectionException here is intentionally left to propagate —
        // that lets Laravel retry the whole job per $tries/$backoff instead
        // of silently giving up on the first transient network blip. The
        // final give-up (after all retries) is recorded once in failed().
        $result = SmeDataPurchaseApi::purchase($this->sim->number, $plan->network, $plan->data_id, $requestId);

        $this->logReport($this->sim, $plan, $requestId, $result['success'], $result['success']
            ? "Activation bonus: {$plan->size} {$plan->plan_type} silently topped up on {$this->sim->number}"
            : ('Activation bonus data top-up failed: ' . ($result['message'] ?? 'Unknown error')));

        if (!$result['success']) {
            Log::warning('Activation bonus data top-up failed', ['sim' => $this->sim->number, 'result' => $result]);
        }
    }

    /**
     * Called once, after every retry attempt has been exhausted (e.g. a
     * sustained vendor outage) — records the final failure so support has
     * an audit trail even though nothing user-facing was ever blocked.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Activation bonus data top-up failed after all retries', [
            'sim'   => $this->sim->number,
            'error' => $exception->getMessage(),
        ]);

        $settings = ActivationBonusSettings::forProvider($this->sim->provider);
        if ($settings->plan) {
            $this->logReport(
                $this->sim,
                $settings->plan,
                RequestIdHelper::generateRequestId(),
                false,
                'Connection error: ' . $exception->getMessage()
            );
        }
    }

    private function logReport(Sim $sim, \App\Models\SmeData $plan, string $ref, bool $success, string $description): void
    {
        // No wallet is touched for this bonus — old/new balance just reflect
        // the recipient's current balance, unchanged, to satisfy the audit trail.
        $balance = \App\Models\Wallet::where('user_id', $sim->user_id)->value('balance') ?? 0.00;

        Report::create([
            'user_id'      => $sim->user_id,
            'phone_number' => $sim->number,
            'network'      => $plan->network,
            'ref'          => $ref,
            'amount'       => 0.00,
            'status'       => $success ? 'completed' : 'failed',
            'type'         => 'activation_bonus_data',
            'description'  => $description,
            'old_balance'  => $balance,
            'new_balance'  => $balance,
        ]);
    }
}
