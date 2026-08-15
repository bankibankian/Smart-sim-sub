<?php

namespace App\Console\Commands;

use App\Jobs\GrantActivationBonusData;
use App\Models\ActivationBonusSettings;
use App\Models\Sim;
use App\Support\SimStatus;
use Illuminate\Console\Command;

/**
 * Bulk, on-demand re-run of the per-activation data bonus: for every
 * network with an active bonus plan configured, queues a
 * GrantActivationBonusData job for every already-activated SIM on that
 * network — same job, same vendor routing, same rate limiting as the
 * normal single-SIM activation path. Intended to be triggered by an
 * external cron entry (no Laravel scheduler wired up in this app), e.g.:
 *   0 9 * * * cd /path/to/app && php artisan sim:grant-bulk-activation-bonus
 */
class GrantBulkActivationBonusData extends Command
{
    protected $signature = 'sim:grant-bulk-activation-bonus';

    protected $description = 'Queue the currently configured activation data bonus for every already-activated SIM, per network';

    public function handle(): int
    {
        $totalQueued = 0;

        foreach (ActivationBonusSettings::PROVIDERS as $provider) {
            $settings = ActivationBonusSettings::forProvider($provider);

            if (!$settings->is_active || !$settings->plan) {
                $this->line("Skipping {$provider}: bonus is off or no plan selected.");
                continue;
            }

            $queued = 0;
            Sim::where('provider', $provider)
                ->where('status', SimStatus::ACTIVATED)
                ->chunkById(200, function ($sims) use (&$queued) {
                    foreach ($sims as $sim) {
                        GrantActivationBonusData::dispatch($sim);
                        $queued++;
                    }
                });

            $this->info("Queued {$queued} SIM(s) on {$provider} for \"{$settings->plan->size} ({$settings->plan->plan_type})\".");
            $totalQueued += $queued;
        }

        $this->info("Total: {$totalQueued} activation bonus job(s) queued.");

        return self::SUCCESS;
    }
}
