<?php

namespace App\Listeners;

use App\Events\SimActivated;
use App\Jobs\GrantActivationBonusData;

/**
 * Kicks off the silent post-activation data bonus (see GrantActivationBonusData)
 * on every SIM activation. Cheap and synchronous — the actual vendor call
 * happens in the queued job, not here.
 */
class GrantActivationBonus
{
    public function handle(SimActivated $event): void
    {
        GrantActivationBonusData::dispatch($event->sim);
    }
}
