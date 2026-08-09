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

    public function __construct(public Sim $sim)
    {
    }

    public function handle(): void
    {
        $settings = ActivationBonusSettings::current();
        if (!$settings->is_active || !$settings->plan) {
            return;
        }

        $plan = $settings->plan;
        $requestId = RequestIdHelper::generateRequestId();

        try {
            $result = SmeDataPurchaseApi::purchase($this->sim->number, $plan->network, $plan->data_id, $requestId);
        } catch (\Exception $e) {
            Log::error('Activation bonus data top-up failed to reach vendor', [
                'sim' => $this->sim->number,
                'error' => $e->getMessage(),
            ]);

            $this->logReport($this->sim, $plan, $requestId, false, 'Connection error: ' . $e->getMessage());

            return;
        }

        $this->logReport($this->sim, $plan, $requestId, $result['success'], $result['success']
            ? "Activation bonus: {$plan->size} {$plan->plan_type} silently topped up on {$this->sim->number}"
            : ('Activation bonus data top-up failed: ' . ($result['message'] ?? 'Unknown error')));

        if (!$result['success']) {
            Log::warning('Activation bonus data top-up failed', ['sim' => $this->sim->number, 'result' => $result]);
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
