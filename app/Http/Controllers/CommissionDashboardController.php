<?php

namespace App\Http\Controllers;

use App\Models\CommissionEarning;
use App\Support\SimAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionDashboardController extends Controller
{
    /**
     * The user's own commission earnings history; admins see every payout.
     * Personal/Business accounts never earn SIM commissions, so they don't get this page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in.');
        }
        if (!SimAccess::canBrowseCatalog($user) && !$user->hasRole('super_admin')) {
            abort(403, 'Commissions are only available to Regional Managers, Coordinators, Partners, and Agents.');
        }

        if ($user->hasRole('super_admin')) {
            $earnings = CommissionEarning::with(['user', 'sim'])->latest()->paginate(20);
            $totalPaid = CommissionEarning::sum('amount');
        } else {
            $earnings = CommissionEarning::with('sim')->where('user_id', $user->id)->latest()->paginate(20);
            $totalPaid = CommissionEarning::where('user_id', $user->id)->sum('amount');
        }

        return view('commissions.dashboard', compact('user', 'earnings', 'totalPaid'));
    }
}
