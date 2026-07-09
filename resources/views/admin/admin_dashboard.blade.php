@push('styles')
    <!-- Bootstrap 5 GRID ONLY for premium responsiveness without Reboot/Reset overrides -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap-grid.min.css" rel="stylesheet" />
    <style>
        /* Premium custom enhancements blending Tailwind/Bootstrap Grid layout */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(66, 81, 124, 0.08);
            border-color: rgba(99, 102, 241, 0.3);
        }
        .font-display {
            font-family: 'Outfit', sans-serif;
        }
        .custom-badge {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .admin-btn {
            border: 1px solid rgba(226, 232, 240, 0.6);
            border-radius: 16px;
            transition: all 0.2s ease;
            text-decoration: none;
            background: #f8fafc;
        }
        .admin-btn:hover {
            background: #42517c;
            color: #ffffff !important;
            border-color: #42517c;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(66, 81, 124, 0.12);
        }
        .admin-btn:hover i {
            color: #ffffff !important;
        }
    </style>
@endpush

<x-app-layout>
    <div class="container-fluid py-2 px-0">
        
        <!-- Header -->
        <div class="row align-items-center mb-4 gy-3">
            <div class="col-12 col-md-8">
                <h1 class="text-3xl font-black font-display text-slate-800 mb-1 flex flex-wrap items-center gap-2">
                    Super Admin Console
                    <span class="badge bg-rose-50 border border-rose-100 text-rose-600 custom-badge rounded-full py-1 px-3">
                        System Admin
                    </span>
                </h1>
                <p class="text-slate-500 text-sm mb-0">Real-time system health, pending operations, transaction metrics, and user management.</p>
            </div>
            
            <div class="col-12 col-md-4 flex justify-start justify-content-md-end">
                <div class="flex items-center gap-2 text-emerald-600 py-2 px-3 bg-white border border-emerald-100 rounded-2xl shadow-sm text-sm">
                    <i data-lucide="shield-check" class="text-emerald-500 w-4 h-4"></i>
                    <span>Operations Active</span>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="p-4 mb-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 flex items-center gap-3 shadow-sm animate-in fade-in" role="alert">
                <i data-lucide="check-circle" class="text-emerald-500 shrink-0 w-5 h-5"></i>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 mb-4 rounded-2xl bg-rose-50 border border-rose-100 text-rose-800 flex items-center gap-3 shadow-sm animate-in fade-in" role="alert">
                <i data-lucide="alert-circle" class="text-rose-500 shrink-0 w-5 h-5"></i>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        <!-- ADMIN KEY PERFORMANCE INDICATORS GRID (KPIs) -->
        <div class="row g-4 mb-4">
            
            <!-- KPI 1: System Users -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="glass-card p-4 h-100 flex flex-col justify-between" style="min-height: 140px;">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-slate-400 text-xs font-bold uppercase block mb-1">System Users</span>
                            <span class="text-3xl font-black font-display text-slate-800 tracking-tight mb-0">{{ number_format($totalUsers) }}</span>
                        </div>
                        <div class="p-2.5 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center w-10 h-10">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-slate-500 mt-3 flex items-center gap-2 text-[10px]" style="font-weight: 500;">
                        <span class="bg-emerald-50 border border-emerald-100 text-emerald-600 rounded py-0.5 px-1.5 font-bold">{{ number_format($activeUsers) }} Active</span>
                        <span>•</span>
                        <span>{{ number_format($totalUsers - $activeUsers) }} Suspended</span>
                    </div>
                </div>
            </div>

            <!-- KPI 2: Circulating Capital -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="glass-card p-4 h-100 flex flex-col justify-between" style="min-height: 140px;">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-slate-400 text-xs font-bold uppercase block mb-1">Circulating Funds</span>
                            <span class="text-xl font-black font-display text-slate-800 tracking-tight mb-0">₦{{ number_format($totalWalletBalance, 2) }}</span>
                        </div>
                        <div class="p-2.5 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center w-10 h-10">
                            <i data-lucide="wallet" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-slate-500 mt-3 flex items-center gap-1.5 text-[10px]" style="font-weight: 500;">
                        <i data-lucide="gift" class="text-amber-500 w-3.5 h-3.5"></i>
                        <span>Bonus pool: ₦{{ number_format($totalWalletBonus, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- KPI 3: System Transactions -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="glass-card p-4 h-100 flex flex-col justify-between" style="min-height: 140px;">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-slate-400 text-xs font-bold uppercase block mb-1">Transaction Volume</span>
                            <span class="text-xl font-black font-display text-slate-800 tracking-tight mb-0">₦{{ number_format($totalTransactionVolume, 2) }}</span>
                        </div>
                        <div class="p-2.5 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center w-10 h-10">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-slate-500 mt-3 flex items-center gap-2 text-[10px]" style="font-weight: 500;">
                        <span class="bg-blue-50 border border-blue-100 text-blue-600 rounded py-0.5 px-1.5 font-bold">{{ number_format($totalTransactionsCount) }} Completed</span>
                        <span>Total counts</span>
                    </div>
                </div>
            </div>

            <!-- KPI 4: Pending Operations -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="glass-card p-4 h-100 flex flex-col justify-between" style="min-height: 140px;">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-slate-400 text-xs font-bold uppercase block mb-1">Pending Requests</span>
                            <span class="text-3xl font-black font-display text-rose-600 tracking-tight mb-0">
                                {{ $pendingUpgradesCount + $pendingSimRequestsCount + $openTicketsCount }}
                            </span>
                        </div>
                        <div class="p-2.5 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center w-10 h-10">
                            <i data-lucide="bell" class="animate-bounce w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="text-slate-500 mt-3 flex items-center gap-1.5 flex-wrap text-[9px]" style="font-weight: 600;">
                        <span class="bg-amber-50 border border-amber-100 text-amber-600 px-1.5 py-0.5 rounded font-bold">{{ $pendingUpgradesCount }} Upgrades</span>
                        <span class="bg-indigo-50 border border-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded font-bold">{{ $pendingSimRequestsCount }} SIMs</span>
                        <span class="bg-rose-50 border border-rose-100 text-rose-600 px-1.5 py-0.5 rounded font-bold">{{ $openTicketsCount }} Tickets</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- QUICK ADMIN ACTIONS PANEL -->
        <div class="glass-card p-4 mb-4">
            <h3 class="text-sm font-bold text-slate-800 font-display mb-3 flex items-center gap-2">
                <i data-lucide="sliders" class="text-indigo-500 w-5 h-5"></i>
                Administrative Quick Actions
            </h3>
            
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-8 g-3">
                <!-- Action 1: Manage Users -->
                <div class="col">
                    <a href="{{ route('admin.manage.users') }}" class="admin-btn p-3 text-center flex flex-col items-center gap-2 h-100">
                        <i data-lucide="users" class="text-slate-800 w-5 h-5"></i>
                        <span class="font-bold text-[11px]">Manage Users</span>
                    </a>
                </div>

                <!-- Action 2: Manage Access -->
                <div class="col">
                    <a href="{{ route('admin.manage.access') }}" class="admin-btn p-3 text-center flex flex-col items-center gap-2 h-100">
                        <i data-lucide="shield" class="text-slate-800 w-5 h-5"></i>
                        <span class="font-bold text-[11px]">Access Control</span>
                    </a>
                </div>

                <!-- Action 3: Upgrade Requests -->
                <div class="col">
                    <a href="{{ route('admin.manage.upgrades') }}" class="admin-btn p-3 text-center flex flex-col items-center gap-2 h-100">
                        <i data-lucide="arrow-up-circle" class="text-slate-800 w-5 h-5"></i>
                        <span class="font-bold text-[11px]">Role Upgrades</span>
                    </a>
                </div>

                <!-- Action 4: SIM Requests -->
                <div class="col">
                    <a href="{{ route('admin.sim-plan.index') }}" class="admin-btn p-3 text-center flex flex-col items-center gap-2 h-100">
                        <i data-lucide="settings" class="text-slate-800 w-5 h-5"></i>
                        <span class="font-bold text-[11px]">SIM Inventory</span>
                    </a>
                </div>

                <!-- Action 5: SME Data Plans -->
                <div class="col">
                    <a href="{{ route('admin.sme-plans.index') }}" class="admin-btn p-3 text-center flex flex-col items-center gap-2 h-100">
                        <i data-lucide="wifi" class="text-slate-800 w-5 h-5"></i>
                        <span class="font-bold text-[11px]">SME Data Plans</span>
                    </a>
                </div>

                <!-- Action 6: Services pricing -->
                <div class="col">
                    <a href="{{ route('admin.services.index') }}" class="admin-btn p-3 text-center flex flex-col items-center gap-2 h-100">
                        <i data-lucide="server" class="text-slate-800 w-5 h-5"></i>
                        <span class="font-bold text-[11px]">Pricing & Fees</span>
                    </a>
                </div>

                <!-- Action 7: System Transactions -->
                <div class="col">
                    <a href="{{ route('admin.transactions') }}" class="admin-btn p-3 text-center flex flex-col items-center gap-2 h-100">
                        <i data-lucide="receipt" class="text-slate-800 w-5 h-5"></i>
                        <span class="font-bold text-[11px]">All Transactions</span>
                    </a>
                </div>

                <!-- Action 8: Support Tickets -->
                <div class="col">
                    <a href="{{ route('admin.manage.support.index') }}" class="admin-btn p-3 text-center flex flex-col items-center gap-2 h-100">
                        <i data-lucide="message-square" class="text-slate-800 w-5 h-5"></i>
                        <span class="font-bold text-[11px]">Support Desk</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- SPLIT LAYOUT: OPERATIONS LISTS -->
        <div class="row g-4">
            
            <!-- Left Side: Transactions & Upgrade Requests (7 cols out of 12) -->
            <div class="col-12 col-lg-7 flex flex-col">
                
                <!-- Recent System Transactions -->
                <div class="glass-card p-4 mb-4">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-slate-800 font-display mb-0 flex items-center gap-2">
                            <i data-lucide="history" class="text-indigo-500 w-5 h-5"></i>
                            Recent System Transactions
                        </h3>
                        <a href="{{ route('admin.transactions') }}" class="no-underline px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 hover:text-indigo-700 text-xs font-bold rounded-lg border border-indigo-100/60 transition shadow-sm">
                            View All
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($recentTransactions as $tx)
                            <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-2xl flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0 flex-grow">
                                    <div class="p-2 rounded-xl {{ $tx->type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center shrink-0 w-8 h-8">
                                        <i data-lucide="{{ $tx->type === 'credit' ? 'arrow-down-left' : 'arrow-up-right' }}" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0 flex-grow">
                                        <h4 class="font-bold text-slate-800 mb-0 truncate block font-display text-xs" title="{{ $tx->description }}">
                                            {{ $tx->description }}
                                        </h4>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-slate-400 mt-0.5 text-[9px] font-bold">
                                            <span class="text-slate-500 font-bold truncate max-w-[120px] sm:max-w-none">{{ $tx->user->email ?? 'N/A' }}</span>
                                            <span class="hidden xs:inline">•</span>
                                            <span class="text-nowrap">{{ $tx->created_at->format('M d, h:i A') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end shrink-0 ml-3">
                                    <span class="block font-display font-bold text-slate-800 text-xs text-nowrap">
                                        ₦{{ number_format($tx->amount, 2) }}
                                    </span>
                                    <span class="bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-full inline-block py-0.5 px-2 text-[8px] font-bold" style="text-transform: uppercase;">
                                        {{ $tx->status }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-slate-400 text-xs">No transactions recorded in the system yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Side: Support Tickets & SIM Requests (5 cols out of 12) -->
            <div class="col-12 col-lg-5 flex flex-col">
                
                <!-- Open Support Tickets -->
                <div class="glass-card p-4 mb-4">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-slate-800 font-display mb-0 flex items-center gap-2">
                            <i data-lucide="message-square" class="text-indigo-500 w-5 h-5"></i>
                            Open Support Desk Tickets
                        </h3>
                        <a href="{{ route('admin.manage.support.index') }}" class="no-underline px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 hover:text-indigo-700 text-xs font-bold rounded-lg border border-indigo-100/60 transition shadow-sm">
                            View All
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($openTickets as $ticket)
                            <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-2xl space-y-2 hover:bg-slate-100/70 transition">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-slate-800 mb-0 text-truncate font-display text-xs" style="max-width: 180px;">
                                            {{ $ticket->subject }}
                                        </h4>
                                        <p class="text-slate-400 mb-0 mt-0.5 text-[9px] font-bold">
                                            User: {{ $ticket->user->email ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <span class="badge text-uppercase py-1 px-1.5 border bg-rose-50 border-rose-100 text-rose-600 rounded text-[8px]" style="font-size: 8px;">
                                        {{ $ticket->priority }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-slate-100 text-[9px] font-bold">
                                    <span class="bg-slate-50 border border-slate-200 text-slate-500 uppercase px-1.5 py-0.5 rounded">{{ $ticket->category }}</span>
                                    <a href="{{ route('admin.manage.support.show', $ticket) }}" class="text-indigo-600 no-underline flex items-center gap-0.5 hover:text-indigo-800">
                                        View & Reply <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-slate-400 text-xs">No open support tickets. Excellent!</div>
                        @endforelse
                    </div>
                </div>

                <!-- Pending SIM Requests -->
                <div class="glass-card p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-slate-800 font-display mb-0 flex items-center gap-2">
                            <i data-lucide="cpu" class="text-indigo-500 w-5 h-5"></i>
                            Pending SIM Orders
                        </h3>
                        <a href="{{ route('admin.sim-plan.index') }}" class="no-underline px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 hover:text-indigo-700 text-xs font-bold rounded-lg border border-indigo-100/60 transition shadow-sm">
                            Manage
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($pendingSimRequests as $simReq)
                            <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-2xl flex items-center justify-between">
                                <div class="min-w-0">
                                    <h4 class="font-bold text-slate-800 mb-0 font-display text-xs">
                                        {{ $simReq->provider }} SIM Request
                                    </h4>
                                    <span class="block text-slate-400 mt-0.5 text-[9px] font-bold">
                                        Num: {{ $simReq->number ?? 'N/A' }} • User: {{ $simReq->user->email ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="text-end">
                                    <span class="block font-display font-bold text-slate-800 text-xs">
                                        ₦{{ number_format($simReq->amount, 2) }}
                                    </span>
                                    <span class="bg-amber-50 border border-amber-100 text-amber-600 px-1.5 py-0.5 rounded mt-0.5 inline-block text-[8px] font-bold uppercase tracking-wider">
                                        {{ $simReq->status }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-slate-400 text-xs">No pending SIM request orders found.</div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
