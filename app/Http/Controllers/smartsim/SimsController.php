<?php

namespace App\Http\Controllers\smartsim;

use App\Http\Controllers\Controller;
use App\Models\Sim;
use App\Models\SimRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SimsController extends Controller
{
    /**
     * Display the SIM Services dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }

        // Fetch categories and pricing dynamically from the database
        $simService = \App\Models\Service::where('name', 'simcard')->first();
        $categories = [];
        if ($simService) {
            foreach ($simService->fields()->active()->get() as $field) {
                $categories[] = [
                    'name'  => $field->field_name,
                    'price' => $field->priceForUser($user),
                ];
            }
        } else {
            foreach (['POS SIM' => 1000.00, 'CAMERA SIM' => 1500.00, 'CCTV' => 2000.00, 'ROUTER SIM' => 2500.00, 'GPS SIM' => 3000.00] as $name => $price) {
                $categories[] = [
                    'name'  => $name,
                    'price' => $price,
                ];
            }
        }
        $providers = ['mtn', 'airtel', 'glo', '9mobile'];

        if ($user->role === 'partner') {
            // Partners fetch SIMs assigned to them (both direct ownership and downline delegation tracking)
            $sims = Sim::with('user')->where('partner_id', $user->id)
                ->latest()->paginate(10, ['*'], 'sims_page');
                
            // Partner can assign to business and agent roles
            $assignableUsers = User::whereIn('role', ['business', 'agent'])
                ->where('status', 'active')
                ->orderBy('first_name')
                ->get();
                
            $requests = SimRequest::with('sim')
                ->where('user_id', $user->id)
                ->latest()->paginate(10, ['*'], 'requests_page');

            return view('smartsimcard.index', compact(
                'user', 'categories', 'providers', 'sims', 
                'assignableUsers', 'requests'
            ));
        }

        // Standard Users (personal, business, agent, staff, checker)
        $sims = Sim::where('user_id', $user->id)
            ->latest()->paginate(10, ['*'], 'sims_page');
            
        $requests = SimRequest::with('sim')
            ->where('user_id', $user->id)
            ->latest()->paginate(10, ['*'], 'requests_page');

        return view('smartsimcard.index', compact(
            'user', 'categories', 'providers', 'sims', 'requests'
        ));
    }

    /**
     * Get available numbers based on category and provider (AJAX endpoint).
     */
    public function getAvailableNumbers(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'provider' => 'required|string',
        ]);

        $numbers = Sim::where('category', $request->category)
            ->where('provider', $request->provider)
            ->where('status', 'available')
            ->orderBy('number')
            ->get(['id', 'number']);

        return response()->json($numbers);
    }

    /**
     * User submits request to purchase/get a SIM number.
     */
    public function requestSim(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'provider' => 'required|string',
            'sim_id'   => 'required|exists:sims,id',
        ]);

        $user = Auth::user();
        $sim = Sim::find($request->sim_id);

        if ($sim->status !== 'available') {
            return back()->with('error', 'The selected SIM number is no longer available.');
        }

        // Check if there is already a pending request for this SIM to prevent double-charging
        $existingRequest = SimRequest::where('sim_id', $sim->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'This SIM card already has a pending request.');
        }

        try {
            DB::transaction(function () use ($user, $sim) {
                // Create the SIM request with 0.00 amount (free request)
                SimRequest::create([
                    'user_id'      => $user->id,
                    'sim_id'       => $sim->id,
                    'number'       => $sim->number,
                    'category'     => $sim->category,
                    'provider'     => $sim->provider,
                    'request_type' => 'purchase',
                    'status'       => 'pending',
                    'amount'       => 0.00,
                ]);
            });

            return back()->with('success', 'Your request for SIM number ' . $sim->number . ' has been submitted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * User submits request to activate their assigned SIM.
     */
    public function activateSim(Request $request)
    {
        $request->validate([
            'sim_id' => 'required|exists:sims,id',
        ]);

        $user = Auth::user();
        $sim = Sim::find($request->sim_id);

        // Ensure the SIM belongs to the user or partner
        if ($sim->user_id !== $user->id && $sim->partner_id !== $user->id) {
            return back()->with('error', 'Access denied. You do not own this SIM card.');
        }

        if ($sim->status === 'active') {
            return back()->with('error', 'This SIM card is already active.');
        }

        // Check if there is already a pending activation request
        $existing = SimRequest::where('sim_id', $sim->id)
            ->where('request_type', 'activation')
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('error', 'There is already a pending activation request for this number.');
        }

        // 1. Resolve pricing
        $simService = \App\Models\Service::where('name', 'simcard')->first();
        $serviceField = null;
        $payableAmount = 0.00;
        if ($simService) {
            $serviceField = \App\Models\ServiceField::where('service_id', $simService->id)
                ->where('field_name', $sim->category)
                ->first();
        }
        if ($serviceField) {
            $payableAmount = $serviceField->priceForUser($user);
        }

        // 2. Database Transaction to charge wallet and create request
        try {
            DB::transaction(function () use ($user, $sim, $payableAmount, $simService) {
                // Lock wallet
                $wallet = \App\Models\Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if (!$wallet || $wallet->balance < $payableAmount) {
                    throw new \Exception('Insufficient wallet balance! You need ₦' . number_format($payableAmount, 2));
                }

                $oldBalance = $wallet->balance;
                $wallet->decrement('balance', $payableAmount);
                $newBalance = $wallet->balance;

                $ref = 'ACT-' . time() . '-' . rand(1000, 9999);

                // Create Transaction record
                \App\Models\Transaction::create([
                    'transaction_ref' => $ref,
                    'user_id'         => $user->id,
                    'amount'          => $payableAmount,
                    'fee'             => 0.00,
                    'net_amount'      => $payableAmount,
                    'description'     => "SIM Card Activation: Request for number {$sim->number} (Category: {$sim->category}, Network: " . strtoupper($sim->provider) . ")",
                    'type'            => 'debit',
                    'status'          => 'completed',
                    'performed_by'    => $user->first_name . ' ' . $user->last_name,
                    'approved_by'     => $user->id,
                ]);

                // Create Report record
                \App\Models\Report::create([
                    'user_id'      => $user->id,
                    'phone_number' => $sim->number,
                    'network'      => $sim->provider,
                    'ref'          => $ref,
                    'amount'       => $payableAmount,
                    'status'       => 'completed',
                    'type'         => 'sim_activation',
                    'description'  => "SIM Card Activation: Request for number {$sim->number} (Category: {$sim->category})",
                    'old_balance'  => $oldBalance,
                    'new_balance'  => $newBalance,
                    'service_id'   => $simService ? $simService->id : null,
                ]);

                // Create the SIM request
                SimRequest::create([
                    'user_id'      => $user->id,
                    'sim_id'       => $sim->id,
                    'number'       => $sim->number,
                    'category'     => $sim->category,
                    'provider'     => $sim->provider,
                    'request_type' => 'activation',
                    'status'       => 'pending',
                    'amount'       => $payableAmount,
                ]);
            });

            $chargeMsg = $payableAmount > 0 ? " ₦" . number_format($payableAmount, 2) . " has been charged from your wallet." : "";
            return back()->with('success', 'Activation request for ' . $sim->number . ' has been submitted successfully.' . $chargeMsg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Partner assigns their assigned SIM to agent or business role.
     */
    public function partnerAssignSim(Request $request)
    {
        $request->validate([
            'sim_id'  => 'required|exists:sims,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $partner = Auth::user();
        $sim = Sim::find($request->sim_id);
        $targetUser = User::find($request->user_id);

        // Security check: SIM must be assigned to partner, and partner must own it
        if ($sim->partner_id !== $partner->id || $sim->user_id !== $partner->id) {
            return back()->with('error', 'You can only assign numbers currently allocated to you.');
        }

        // Security check: Target user must be agent or business
        if (!in_array($targetUser->role, ['agent', 'business'])) {
            return back()->with('error', 'You can only assign numbers to agent or business accounts.');
        }

        $sim->update([
            'user_id' => $targetUser->id,
            'status'  => 'assigned',
        ]);

        return back()->with('success', "SIM number {$sim->number} successfully assigned to {$targetUser->first_name} {$targetUser->last_name}.");
    }

    /**
     * Public/User SIM lookup check.
     */
    public function checkNumber(Request $request)
    {
        $request->validate([
            'number' => 'required|string',
        ]);

        $sim = Sim::with('user')->where('number', $request->number)->first();

        if (!$sim) {
            return back()->with('check_result', [
                'success' => false,
                'message' => 'SIM number not found in the database.',
            ]);
        }

        if ($sim->user_id && $sim->user) {
            $isOwnerOrAdmin = ($sim->user_id === Auth::id()) || (Auth::user() && Auth::user()->role === 'super_admin');
            
            return back()->with('check_result', [
                'success'      => true,
                'found'        => true,
                'assigned'     => true,
                'number'       => $sim->number,
                'category'     => $sim->category,
                'provider'     => $sim->provider,
                'status'       => $sim->status,
                'user_name'    => $isOwnerOrAdmin ? ($sim->user->first_name . ' ' . $sim->user->last_name) : 'Masked (Unauthorized)',
                'user_email'   => $isOwnerOrAdmin ? $sim->user->email : 'Masked (Unauthorized)',
                'user_phone'   => $isOwnerOrAdmin ? $sim->user->phone : 'Masked (Unauthorized)',
            ]);
        }

        return back()->with('check_result', [
            'success'  => true,
            'found'    => true,
            'assigned' => false,
            'number'   => $sim->number,
            'category' => $sim->category,
            'provider' => $sim->provider,
            'status'   => $sim->status,
        ]);
    }
}
