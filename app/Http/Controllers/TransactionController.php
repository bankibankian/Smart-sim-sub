<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a listing of the user's transactions.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $query = Transaction::where('user_id', $user->id)->orderBy('id', 'desc');

        // Filter by Transaction Type (credit, debit, refund)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Global Keyword Search (Description, Ref, Metadata, Amount)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%$search%")
                  ->orWhere('transaction_ref', 'like', "%$search%")
                  ->orWhere('metadata', 'like', "%$search%");
                
                // Numeric search for amount
                $numericSearch = str_replace([',', '₦'], '', $search);
                if (is_numeric($numericSearch)) {
                    $q->orWhere('amount', 'like', "%$numericSearch%");
                }
            });
        }

        // Filter by Date Range (Backup logic)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(10);

        return view('transactions', compact('transactions'));
    }
    /**
     * Display a specific transaction receipt.
     * Supports both dynamic DB lookup and session-based fallback for backward compatibility.
     */
    public function receipt(Request $request)
    {
        $ref = $request->query('ref') ?? session('ref');
        
        // Initial defaults from session or empty
        $data = [
            'ref' => $ref ?? 'N/A',
            'token' => session('token'),
            'serial' => session('serial'),
            'amount' => session('amount') ?? 0,
            'fee' => session('fee') ?? 0,
            'tax' => 0,
            'paid' => session('paid') ?? session('amount') ?? 0,
            'network' => session('network') ?? 'N/A',
            'mobile' => session('mobile') ?? 'N/A',
            'date' => session('date') ?? now(),
            'receiverName' => null,
            'serviceName' => 'Service Purchase',
            'status' => 'completed',
        ];
 
        // Attempt to fetch robust data from DB if ref exists
        if ($ref && $ref !== 'N/A') {
            $tx = Transaction::where('transaction_ref', $ref)->first();
            if ($tx) {
                if ($tx->user_id !== Auth::id() && Auth::user()->role !== 'super_admin') {
                    abort(403, 'Unauthorized access to this transaction receipt.');
                }
                $data['amount'] = $tx->amount;
                $data['paid'] = $tx->net_amount;
                $data['fee'] = $tx->fee ?? 0;
                $data['date'] = $tx->created_at;
                $data['ref'] = $tx->transaction_ref;
                $data['status'] = $tx->status;
                
                $meta = is_array($tx->metadata) ? $tx->metadata : json_decode($tx->metadata ?? '[]', true);
                
                if (isset($meta['service']) && $meta['service'] === 'withdrawal' || str_starts_with($data['ref'], 'WDL')) {
                    $data['serviceName'] = 'Wallet Withdrawal';
                    $data['network'] = $meta['bankName'] ?? 'Bank Transfer';
                    $data['mobile'] = $meta['account_no'] ?? 'N/A';
                    $data['receiverName'] = $meta['account_name'] ?? null;

                    // $tx->amount is the gross total charged (base + fee) for
                    // withdrawals, not the base amount — show the base amount
                    // here so Amount + Fee visibly sums to Total Debited
                    // instead of double-counting the fee on screen.
                    $data['amount'] = $meta['price_details']['amount'] ?? ($tx->amount - $data['fee']);

                    // Query for associated withdrawal tax transaction
                    $taxTx = Transaction::where('user_id', $tx->user_id)
                        ->where('type', 'debit')
                        ->where('metadata->service', 'withdrawal_tax')
                        ->where('metadata->withdrawal_ref', $ref)
                        ->first();
                    $data['tax'] = $taxTx ? $taxTx->amount : 0;
                    $data['paid'] = $tx->net_amount + $data['tax'];
                } else {
                    $data['network'] = $meta['network'] ?? $data['network'];
                    $data['mobile'] = $meta['phone_number'] ?? $meta['account_number'] ?? $data['mobile'];
                    $data['token'] = $meta['token'] ?? $meta['purchased_code'] ?? $meta['purchased_pin'] ?? $data['token'];
                    $data['serial'] = $meta['serial_number'] ?? $data['serial'];

                    // Dynamic Service Name identification
                    if ($data['token']) {
                        $data['serviceName'] = 'Educational Pin';
                    } elseif (str_contains(strtolower($data['network']), 'data')) {
                        $data['serviceName'] = 'Data Purchase';
                    } elseif ($data['network'] !== 'N/A') {
                        $data['serviceName'] = 'Airtime Purchase';
                    }
                }
            }
        }

        return view('thankyou', $data);
    }
}
