<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sim;
use App\Models\SimSwapRequest;
use App\Models\User;
use App\Services\SimAssignmentService;
use App\Support\SimStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SimSwapController extends Controller
{
    /**
     * Admin approves a pending swap request — re-validates everything that
     * could have drifted since the request was filed before actually
     * moving the SIM.
     */
    public function approve(Request $request, SimSwapRequest $simSwapRequest)
    {
        if ($simSwapRequest->status !== 'pending') {
            return back()->with('error', 'This swap request has already been processed.');
        }

        $actor = $request->user();

        try {
            DB::transaction(function () use ($simSwapRequest, $actor) {
                $locked = SimSwapRequest::where('id', $simSwapRequest->id)->lockForUpdate()->first();
                if (!$locked || $locked->status !== 'pending') {
                    throw new \Exception('This swap request has already been processed.');
                }

                $sim = Sim::where('id', $locked->sim_id)->lockForUpdate()->first();
                $toHolder = User::find($locked->to_holder_id);
                $fromHolder = User::find($locked->from_holder_id);

                if (!$sim || !$toHolder || !$fromHolder) {
                    throw new \Exception('The SIM or one of the accounts involved no longer exists.');
                }
                if ($toHolder->status !== 'active' || $fromHolder->status !== 'active') {
                    throw new \Exception('One of the accounts involved is no longer active.');
                }
                if ($toHolder->role !== $locked->holder_role) {
                    throw new \Exception("The target account's role has changed since this request was filed.");
                }

                // The SIM may have moved on (cascaded further, activated, or
                // already swapped) since the request was filed.
                $stillHeld = match ($locked->holder_role) {
                    'coordinator' => $sim->status === SimStatus::ASSIGNED_TO_COORDINATOR && $sim->coordinator_id === $fromHolder->id,
                    'partner' => $sim->status === SimStatus::ASSIGNED_TO_PARTNER && $sim->user_id === $sim->partner_id && $sim->partner_id === $fromHolder->id,
                    'personal' => $sim->status === SimStatus::ASSIGNED_TO_PARTNER && $sim->user_id === $fromHolder->id,
                    default => false,
                };

                if (!$stillHeld) {
                    throw new \Exception('This SIM has moved since the swap was requested and can no longer be swapped.');
                }

                app(SimAssignmentService::class)->swapHolder($sim, $toHolder, $actor);

                $locked->update([
                    'status' => 'approved',
                    'approved_by' => $actor->id,
                    'resolved_at' => now(),
                    'admin_notes' => 'Approved.',
                ]);
            });

            return back()->with('success', 'Swap approved and the SIM has been reassigned.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin rejects a pending swap request. No money moves here — this is
     * simpler than rejecting a purchase/cash-out request.
     */
    public function reject(Request $request, SimSwapRequest $simSwapRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        if ($simSwapRequest->status !== 'pending') {
            return back()->with('error', 'This swap request has already been processed.');
        }

        try {
            DB::transaction(function () use ($simSwapRequest, $request) {
                $locked = SimSwapRequest::where('id', $simSwapRequest->id)->lockForUpdate()->first();
                if (!$locked || $locked->status !== 'pending') {
                    throw new \Exception('This swap request has already been processed.');
                }

                $locked->update([
                    'status' => 'rejected',
                    'admin_notes' => $request->admin_notes ?: 'Rejected.',
                    'approved_by' => Auth::id(),
                    'resolved_at' => now(),
                ]);
            });

            return back()->with('success', 'Swap request rejected.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error rejecting request: ' . $e->getMessage());
        }
    }
}
