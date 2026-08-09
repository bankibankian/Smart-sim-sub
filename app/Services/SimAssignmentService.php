<?php

namespace App\Services;

use App\Events\SimActivated;
use App\Models\Sim;
use App\Models\SimHistory;
use App\Models\User;
use App\Support\RoleHierarchy;
use App\Support\SimStatus;
use Illuminate\Support\Facades\DB;

/**
 * Single place for every SIM status transition: the admin -> Regional Manager
 * -> Coordinator -> Partner -> User assignment cascade, plus activation and
 * the admin lifecycle actions (deactivate/suspend/unassign).
 */
class SimAssignmentService
{
    /**
     * Admin assigns a currently-unassigned SIM to a user. Which FK/status it
     * lands on depends on the target's role in the hierarchy.
     */
    public function adminAssign(Sim $sim, User $target, User $actor): void
    {
        if ($sim->status !== SimStatus::UNASSIGNED) {
            throw new \RuntimeException('Only unassigned SIMs can be assigned this way.');
        }

        DB::transaction(function () use ($sim, $target, $actor) {
            $fields = match ($target->role) {
                'regional_manager' => ['regional_manager_id' => $target->id, 'status' => SimStatus::ASSIGNED_TO_RM],
                'coordinator' => ['coordinator_id' => $target->id, 'status' => SimStatus::ASSIGNED_TO_COORDINATOR],
                default => ['user_id' => $target->id, 'partner_id' => $target->role === 'partner' ? $target->id : null, 'status' => SimStatus::ASSIGNED_TO_PARTNER],
            };

            $from = $sim->status;
            $sim->update($fields);
            $this->logHistory($sim, $from, $sim->status, $actor, "Admin assigned to {$target->name} ({$target->role})");
        });
    }

    /**
     * $from delegates a SIM they currently hold down to their direct subordinate $to
     * (Regional Manager -> Coordinator, Coordinator -> Partner, Partner -> User).
     */
    public function assignDown(Sim $sim, User $from, User $to): void
    {
        $expectedNextRole = RoleHierarchy::nextRole($from->role);
        if (!$expectedNextRole || $expectedNextRole !== $to->role) {
            throw new \RuntimeException("You can only assign to a " . str_replace('_', ' ', (string) $expectedNextRole) . '.');
        }

        $holds = match ($from->role) {
            'regional_manager' => $sim->regional_manager_id === $from->id && $sim->status === SimStatus::ASSIGNED_TO_RM,
            'coordinator' => $sim->coordinator_id === $from->id && $sim->status === SimStatus::ASSIGNED_TO_COORDINATOR,
            'partner' => $sim->partner_id === $from->id && $sim->status === SimStatus::ASSIGNED_TO_PARTNER,
            default => false,
        };

        if (!$holds) {
            throw new \RuntimeException('You do not currently hold this SIM at the required stage.');
        }

        DB::transaction(function () use ($sim, $from, $to) {
            $fields = match ($to->role) {
                'coordinator' => ['coordinator_id' => $to->id, 'status' => SimStatus::ASSIGNED_TO_COORDINATOR],
                'partner' => ['partner_id' => $to->id, 'user_id' => $to->id, 'status' => SimStatus::ASSIGNED_TO_PARTNER],
                'personal' => ['user_id' => $to->id, 'status' => SimStatus::ASSIGNED_TO_PARTNER],
                default => throw new \RuntimeException('Invalid target role.'),
            };

            $fromStatus = $sim->status;
            $sim->update($fields);
            $this->logHistory($sim, $fromStatus, $sim->status, $from, "Delegated to {$to->name} ({$to->role})");
        });
    }

    /**
     * Mark a SIM active, whether triggered by admin approval of a user's
     * activation request or a direct admin action. Fires SimActivated so the
     * Commission Engine can pay out the chain.
     */
    public function activate(Sim $sim, User $actor): void
    {
        if ($sim->status === SimStatus::ACTIVATED) {
            throw new \RuntimeException('This SIM is already activated.');
        }

        DB::transaction(function () use ($sim, $actor) {
            $from = $sim->status;
            $sim->update(['status' => SimStatus::ACTIVATED]);
            $this->logHistory($sim, $from, SimStatus::ACTIVATED, $actor, 'SIM activated.');
        });

        event(new SimActivated($sim->fresh(), $actor));
    }

    public function deactivate(Sim $sim, User $actor): void
    {
        $from = $sim->status;
        $sim->update(['status' => SimStatus::DEACTIVATED]);
        $this->logHistory($sim, $from, SimStatus::DEACTIVATED, $actor, 'SIM deactivated.');
    }

    public function suspend(Sim $sim, User $actor): void
    {
        $from = $sim->status;
        $sim->update(['status' => SimStatus::SUSPENDED]);
        $this->logHistory($sim, $from, SimStatus::SUSPENDED, $actor, 'SIM suspended.');
    }

    public function unassign(Sim $sim, User $actor): void
    {
        $from = $sim->status;
        $sim->update([
            'user_id' => null,
            'partner_id' => null,
            'coordinator_id' => null,
            'regional_manager_id' => null,
            'status' => SimStatus::UNASSIGNED,
        ]);
        $this->logHistory($sim, $from, SimStatus::UNASSIGNED, $actor, 'SIM unassigned.');
    }

    private function logHistory(Sim $sim, ?string $from, string $to, User $actor, string $note): void
    {
        SimHistory::create([
            'sim_id' => $sim->id,
            'from_status' => $from,
            'to_status' => $to,
            'actor_id' => $actor->id,
            'note' => $note,
        ]);
    }
}
