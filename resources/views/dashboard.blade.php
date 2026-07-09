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
        .glow-indigo {
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.1);
        }
        .glow-amber {
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.1);
        }
        .glow-emerald {
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.1);
        }
        .service-btn {
            border: 1px solid rgba(226, 232, 240, 0.6);
            border-radius: 20px;
            transition: all 0.3s ease;
            text-decoration: none;
            background: #f8fafc;
        }
        .service-btn:hover {
            background: #ffffff;
            border-color: rgba(99, 102, 241, 0.25);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.06);
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
    </style>
@endpush

<x-app-layout>
    <div class="container-fluid py-2 px-0">
        
        <!-- Welcome Greeting & Date Header -->
        <div class="row align-items-center mb-4 gy-3">
            <div class="col-12 col-md-8">
                <h1 class="text-3xl font-black font-display text-slate-800 mb-1 flex flex-wrap items-center gap-2">
                    Welcome back, {{ Auth::user()->first_name ?: Auth::user()->email }}!
                    <span class="badge bg-indigo-50 border border-indigo-100 text-indigo-600 custom-badge rounded-full py-1 px-3">
                        {{ Auth::user()->role }}
                    </span>
                </h1>
                <p class="text-slate-500 text-sm mb-0">Here is your account overview and recent activity for today, {{ now()->format('F d, Y') }}.</p>
            </div>
            
            <div class="col-12 col-md-4 flex justify-start justify-content-md-end">
                <div class="flex items-center gap-2 text-slate-500 py-2 px-3 bg-white border border-slate-200 rounded-2xl shadow-sm text-sm">
                    <i data-lucide="clock" class="text-indigo-500 animate-pulse w-4 h-4"></i>
                    <span>Last login: {{ Auth::user()->last_login_at ? Auth::user()->last_login_at->format('M d, h:i A') : now()->format('M d, h:i A') }}</span>
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

        <!-- TOP FINANCIAL GRID (Balances & Virtual Accounts using Bootstrap grid) -->
        <div class="row g-4 mb-4">
            
            <!-- Card 1: Main Wallet Balance -->
            <div class="col-12 col-md-4">
                <div class="glass-card glow-indigo p-4 h-100 flex flex-col justify-between" style="min-height: 220px;">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center w-10 h-10">
                                    <i data-lucide="wallet" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800 font-display mb-0">Wallet Balance</h3>
                                    <span class="bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-full mt-1 inline-block py-0.5 px-2 text-[8px] font-bold">Available for Use</span>
                                </div>
                            </div>
                            <i data-lucide="circle-ellipsis" class="text-slate-400 w-5 h-5"></i>
                        </div>

                        <div class="py-2">
                            <span class="text-slate-400 text-sm block mb-1">Total Available funds</span>
                            <div class="text-3xl font-extrabold font-display text-slate-800 tracking-tight mb-0">
                                ₦{{ number_format($walletData['balance'] ?? 0.00, 2) }}
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3 flex items-center justify-between text-sm mt-3">
                        <span class="text-slate-400">Main Account</span>
                        <a href="{{ route('transfer') }}" class="text-indigo-600 font-bold no-underline flex items-center gap-1 hover:text-indigo-800">
                            Send Cash <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 2: Rewards & Bonus -->
            <div class="col-12 col-md-4">
                <div class="glass-card glow-amber p-4 h-100 flex flex-col justify-between" style="min-height: 220px;">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center w-10 h-10">
                                    <i data-lucide="gift" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800 font-display mb-0">Referral & Bonuses</h3>
                                    <span class="bg-amber-50 border border-amber-100 text-amber-600 rounded-full mt-1 inline-block py-0.5 px-2 text-[8px] font-bold">Claimable Earnings</span>
                                </div>
                            </div>
                            <i data-lucide="award" class="text-amber-500 w-5 h-5"></i>
                        </div>

                        <div class="py-2">
                            <span class="text-slate-400 text-sm block mb-1">Accumulated Rewards</span>
                            <div class="text-3xl font-extrabold font-display text-slate-800 tracking-tight mb-0">
                                ₦{{ number_format($walletData['bonus'] ?? 0.00, 2) }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 mt-3">
                        @if(isset($walletData) && $walletData['bonus'] > 0)
                            <form method="POST" action="{{ route('wallet.claimBonus') }}" class="w-full m-0">
                                @csrf
                                <button type="submit" class="w-full py-2.5 font-bold text-white flex items-center justify-center gap-1.5 font-display border-0 rounded-2xl shadow-sm hover:scale-[1.02] transition" style="background: linear-gradient(135deg, #f59e0b, #ea580c); font-size: 12px;">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    Claim to Main Wallet
                                </button>
                            </form>
                        @else
                            <div class="w-full bg-slate-50 border border-slate-100 rounded-2xl py-2.5 text-center text-slate-400 text-xs flex items-center justify-center gap-1.5 font-bold" style="font-size: 10px;">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                Spend to unlock more bonuses
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card 3: Virtual Account Details -->
            <div class="col-12 col-md-4">
                <div class="glass-card glow-emerald p-4 h-100 flex flex-col justify-between" style="min-height: 220px;">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center w-10 h-10">
                                    <i data-lucide="building-2" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800 font-display mb-0">Instant Funding</h3>
                                    <span class="bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-full mt-1 inline-block py-0.5 px-2 text-[8px] font-bold">PalmPay settlement</span>
                                </div>
                            </div>
                            <i data-lucide="shield-check" class="text-emerald-500 w-5 h-5"></i>
                        </div>

                        @if($virtualAccount)
                            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                                <div class="flex justify-between items-center text-slate-400" style="font-size: 10px; font-weight: 500;">
                                    <span>BANK</span>
                                    <span class="font-bold text-slate-800">{{ $virtualAccount->bank_name ?? ($virtualAccount->bankName ?? 'PalmPay') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-400" style="font-size: 10px; font-weight: 500;">
                                    <span>NAME</span>
                                    <span class="font-bold text-slate-800 text-truncate" style="max-width: 140px;">{{ $virtualAccount->account_name ?? ($virtualAccount->accountName ?? 'N/A') }}</span>
                                </div>
                                
                                <div class="border-t border-slate-100 pt-2 mt-2 flex items-center justify-between" x-data="{ copied: false }">
                                    <span class="text-xl font-bold text-indigo-600 font-display mb-0 tracking-wider" x-ref="accNum">
                                        {{ $virtualAccount->account_number ?? ($virtualAccount->accountNo ?? 'N/A') }}
                                    </span>
                                    <button @click="navigator.clipboard.writeText($refs.accNum.innerText.trim()); copied = true; setTimeout(() => copied = false, 2000)"
                                            type="button"
                                            class="bg-white border border-slate-200 py-1 px-2 text-slate-500 rounded-lg flex items-center gap-1 hover:text-indigo-600 hover:border-indigo-100" style="font-size: 9px; font-weight: 700;">
                                        <i x-show="!copied" data-lucide="copy" class="w-3 h-3"></i>
                                        <i x-show="copied" data-lucide="check" class="text-emerald-500 w-3 h-3" style="display:none;"></i>
                                        <span x-text="copied ? 'Copied' : 'Copy'">Copy</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-2">
                                <a href="{{ route('wallet') }}" class="w-full py-2.5 font-bold rounded-2xl shadow-sm inline-flex items-center justify-center gap-1.5 font-display text-white text-decoration-none" style="background: linear-gradient(135deg, #4f46e5, #4338ca); border: none; font-size: 11px;">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                    <span class="text-nowrap">Setup Funding Account</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="text-slate-400 mt-2 text-[9px]" style="font-weight: 500;">
                        * Transfer to this account to fund your main wallet balance instantly.
                    </div>
                </div>
            </div>

        </div>

        <!-- QUICK SERVICES PANEL (5 core actions on one line using Bootstrap row-cols-3 and row-cols-md-5) -->
        <div class="glass-card p-4 mb-4">
            <h3 class="text-sm font-bold text-slate-800 font-display mb-3 flex items-center gap-2">
                <i data-lucide="grid" class="text-indigo-500 w-5 h-5"></i>
                Quick Transaction Services
            </h3>
            
            <div class="row row-cols-3 row-cols-md-5 g-2 g-md-4 justify-content-center">
                <!-- Service 1: Buy Airtime -->
                <div class="col">
                    <a href="{{ route('airtime') }}" class="service-btn p-3 p-md-4 text-center flex flex-col items-center gap-2 h-100">
                        <div class="p-2 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center transition-all w-10 h-10">
                            <i data-lucide="phone" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 mb-0 font-display text-xs sm:text-sm">Buy Airtime</h4>
                            <p class="text-slate-400 hidden md:block mb-0 mt-1 text-[9px]">Top-up instantly</p>
                        </div>
                    </a>
                </div>

                <!-- Service 2: Buy Data -->
                <div class="col">
                    <a href="{{ route('buy-sme-data') }}" class="service-btn p-3 p-md-4 text-center flex flex-col items-center gap-2 h-100">
                        <div class="p-2 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center transition-all w-10 h-10">
                            <i data-lucide="wifi" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 mb-0 font-display text-xs sm:text-sm">Buy Data</h4>
                            <p class="text-slate-400 hidden md:block mb-0 mt-1 text-[9px]">SME & Retail data</p>
                        </div>
                    </a>
                </div>

                <!-- Service 3: SIM Services -->
                <div class="col">
                    <a href="{{ route('sims.index') }}" class="service-btn p-3 p-md-4 text-center flex flex-col items-center gap-2 h-100">
                        <div class="p-2 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center transition-all w-10 h-10">
                            <i data-lucide="cpu" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 mb-0 font-display text-xs sm:text-sm">SIM Plan</h4>
                            <p class="text-slate-400 hidden md:block mb-0 mt-1 text-[9px]">Activations & SIMs</p>
                        </div>
                    </a>
                </div>

                <!-- Service 4: Withdrawal -->
                <div class="col">
                    <a href="{{ route('withdraw') }}" class="service-btn p-3 p-md-4 text-center flex flex-col items-center gap-2 h-100">
                        <div class="p-2 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center transition-all w-10 h-10">
                            <i data-lucide="banknote" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 mb-0 font-display text-xs sm:text-sm">Withdraw</h4>
                            <p class="text-slate-400 hidden md:block mb-0 mt-1 text-[9px]">Pay out to bank</p>
                        </div>
                    </a>
                </div>

                <!-- Service 5: P2P Transfer -->
                <div class="col">
                    <a href="{{ route('transfer') }}" class="service-btn p-3 p-md-4 text-center flex flex-col items-center gap-2 h-100">
                        <div class="p-2 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center transition-all w-10 h-10">
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 mb-0 font-display text-xs sm:text-sm">P2P Send</h4>
                            <p class="text-slate-400 hidden md:block mb-0 mt-1 text-[9px]">Free P2P Transfer</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- SPLIT LAYOUT: STATISTICS (Left) & RECENT TRANSACTIONS (Right) -->
        <div class="row g-4">
            
            <!-- Left Side: Transaction Statistics (5 cols out of 12) -->
            <div class="col-12 col-lg-5 flex flex-col">
                <div class="glass-card p-4 flex flex-col justify-between h-100 flex-grow">
                    
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-sm font-bold text-slate-800 font-display mb-0 flex items-center gap-2">
                                <i data-lucide="bar-chart-2" class="text-indigo-500 w-5 h-5"></i>
                                Account Activity Statistics
                            </h3>
                            <span class="badge bg-slate-50 border border-slate-200 text-slate-500 text-[8px] font-bold uppercase py-1 px-2 rounded">Last 7 Days</span>
                        </div>

                        <!-- Mini stats summary grid -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-2xl">
                                    <div class="flex items-center gap-1.5 text-emerald-600 font-display font-bold text-[9px] uppercase">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3"></i>
                                        Total Funded
                                    </div>
                                    <div class="text-lg font-extrabold text-slate-800 font-display mb-0 mt-1">
                                        ₦{{ number_format($totalCredits, 2) }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="p-3 bg-rose-50 border border-rose-100 rounded-2xl">
                                    <div class="flex items-center gap-1.5 text-rose-600 font-display font-bold text-[9px] uppercase">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                        Total Spent
                                    </div>
                                    <div class="text-lg font-extrabold text-slate-800 font-display mb-0 mt-1">
                                        ₦{{ number_format($totalDebits, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SVG-based Custom Dual-Bar Chart -->
                        <div class="bg-slate-50 border border-slate-200/60 rounded-2xl p-3">
                            <div class="flex justify-between items-center text-slate-400 mb-3 text-[9px] font-bold">
                                <span>Volume Trend</span>
                                <div class="flex items-center gap-2">
                                    <span class="flex items-center gap-1 text-emerald-600"><span class="bg-emerald-500 rounded-sm inline-block w-2.5 h-2.5"></span> Cash-In</span>
                                    <span class="flex items-center gap-1 text-rose-600"><span class="bg-rose-500 rounded-sm inline-block w-2.5 h-2.5"></span> Cash-Out</span>
                                </div>
                            </div>

                            @php
                                $maxVal = 1000;
                                foreach ($chartData as $day) {
                                    if ($day['credits'] > $maxVal) $maxVal = $day['credits'];
                                    if ($day['debits'] > $maxVal) $maxVal = $day['debits'];
                                }
                            @endphp

                            <!-- Responsive SVG Chart container -->
                            <div class="position-relative w-100" style="height: 140px;">
                                <svg class="w-full h-full" viewBox="0 0 350 140" preserveAspectRatio="none">
                                    <!-- horizontal gridlines -->
                                    <line x1="0" y1="35" x2="350" y2="35" stroke="#e2e8f0" stroke-width="1" />
                                    <line x1="0" y1="70" x2="350" y2="70" stroke="#e2e8f0" stroke-width="1" />
                                    <line x1="0" y1="105" x2="350" y2="105" stroke="#e2e8f0" stroke-width="1" />
                                    
                                    <!-- Bars mapping -->
                                    @foreach ($chartData as $index => $day)
                                        @php
                                            $colWidth = 50; 
                                            $startX = ($index * $colWidth) + 12;
                                            
                                            $creditHeight = ($day['credits'] / $maxVal) * 105;
                                            $debitHeight = ($day['debits'] / $maxVal) * 105;
                                            
                                            if ($day['credits'] > 0 && $creditHeight < 4) $creditHeight = 4;
                                            if ($day['debits'] > 0 && $debitHeight < 4) $debitHeight = 4;
                                            
                                            $creditY = 115 - $creditHeight;
                                            $debitY = 115 - $debitHeight;
                                        @endphp
                                        
                                        <!-- Credit Bar -->
                                        <rect x="{{ $startX }}" y="{{ $creditY }}" width="10" height="{{ $creditHeight }}" rx="2" fill="#10b981">
                                            <title>{{ $day['day'] }}: Funded ₦{{ number_format($day['credits'], 2) }}</title>
                                        </rect>
                                        
                                        <!-- Debit Bar -->
                                        <rect x="{{ $startX + 12 }}" y="{{ $debitY }}" width="10" height="{{ $debitHeight }}" rx="2" fill="#f43f5e">
                                            <title>{{ $day['day'] }}: Spent ₦{{ number_format($day['debits'], 2) }}</title>
                                        </rect>

                                        <!-- Day Labels -->
                                        <text x="{{ $startX + 11 }}" y="132" fill="#94a3b8" font-size="9" font-weight="bold" text-anchor="middle">
                                            {{ $day['day'] }}
                                        </text>
                                    @endforeach

                                    <!-- Bottom axis line -->
                                    <line x1="0" y1="118" x2="350" y2="118" stroke="#cbd5e1" stroke-width="1" />
                                </svg>
                            </div>
                        </div>

                    </div>

                    <!-- Milestone card -->
                    <div class="p-3 border-0 rounded-2xl text-white relative overflow-hidden" style="background: linear-gradient(135deg, #42517c, #55699e);">
                        <div class="absolute rounded-full bg-white bg-opacity-5" style="width: 80px; height: 80px; top: -30px; right: -30px; filter: blur(20px);"></div>
                        <div class="relative space-y-2">
                            <div class="flex justify-between items-center mb-1 text-[10px] font-bold">
                                <span>Monthly Spend Milestone</span>
                                <span>{{ $bonusProgress }}%</span>
                            </div>
                            <div class="w-full bg-white bg-opacity-20 rounded-full" style="height: 6px;">
                                <div class="bg-amber-400 rounded-full" style="height: 6px; width: {{ $bonusProgress }}%;"></div>
                            </div>
                            <p class="mb-0 mt-2 text-white-50 text-[10px]" style="line-height: 1.4;">
                                Spend ₦{{ number_format($currentSpend, 2) }} of ₦{{ number_format($nextBonusTarget, 2) }} limit. Reach target to claim a cashback coupon!
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right Side: Recent Transactions (7 cols out of 12) -->
            <div class="col-12 col-lg-7 flex flex-col">
                <div class="glass-card p-4 flex flex-col justify-between h-100 flex-grow">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-sm font-bold text-slate-800 font-display mb-0 flex items-center gap-2">
                                <i data-lucide="history" class="text-indigo-500 w-5 h-5"></i>
                                Recent Transactions
                            </h3>
                            <a href="{{ route('transactions') }}" class="no-underline px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 hover:text-indigo-700 text-xs font-bold rounded-lg border border-indigo-100/60 transition shadow-sm">
                                View All
                            </a>
                        </div>

                        <!-- Transactions List -->
                        <div class="overflow-y-auto pr-1" style="max-height: 380px;">
                            @forelse($recentTransactions as $tx)
                                <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-2xl flex items-center justify-between mb-2 transition-all hover:bg-slate-100/70">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-grow">
                                        <!-- Type icon -->
                                        @if($tx->type === 'credit')
                                            <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 shrink-0 flex items-center justify-center w-8 h-8">
                                                <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                                            </div>
                                        @else
                                            <div class="p-2 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 shrink-0 flex items-center justify-center w-8 h-8">
                                                <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                                            </div>
                                        @endif
                                        
                                        <!-- Details -->
                                        <div class="min-w-0 flex-grow">
                                            <h4 class="font-bold text-slate-800 mb-0 truncate block text-xs" title="{{ $tx->description }}">
                                                {{ $tx->description }}
                                            </h4>
                                            <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-slate-400 mt-0.5 text-[9px] font-bold">
                                                <span class="truncate block max-w-[80px] sm:max-w-none" title="{{ $tx->transaction_ref }}">{{ $tx->transaction_ref }}</span>
                                                <span>•</span>
                                                <span class="text-nowrap">{{ $tx->created_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Amount & Status Badge -->
                                    <div class="text-end shrink-0 ml-3">
                                        <span class="block font-display font-bold text-slate-800 text-nowrap text-xs">
                                            {{ $tx->type === 'credit' ? '+' : '-' }}₦{{ number_format($tx->amount, 2) }}
                                        </span>
                                        
                                        @if($tx->status === 'completed')
                                            <span class="bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-full mt-1 inline-block py-0.5 px-2 text-[8px] font-bold">
                                                Completed
                                            </span>
                                        @elseif($tx->status === 'pending')
                                            <span class="bg-amber-50 border border-amber-100 text-amber-600 rounded-full mt-1 inline-block py-0.5 px-2 text-[8px] font-bold">
                                                Pending
                                            </span>
                                        @else
                                            <span class="bg-rose-50 border border-rose-100 text-rose-600 rounded-full mt-1 inline-block py-0.5 px-2 text-[8px] font-bold">
                                                Failed
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 bg-slate-50 border border-slate-200/60 rounded-2xl">
                                    <div class="p-3 bg-white rounded-full inline-flex items-center justify-center text-slate-400 mb-3 w-12 h-12">
                                        <i data-lucide="receipt" class="w-5 h-5"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-xs">No Transactions Yet</h4>
                                    <p class="text-slate-400 text-[11px] mt-1 max-w-xs mx-auto mb-0">Fund your wallet or buy packages to see transaction statements here.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Footer Help -->
                    <div class="border-t border-slate-100 pt-3 flex items-center justify-between mt-3 text-slate-400 text-[9px] font-bold">
                        <span>Need a receipt? Click transaction in history.</span>
                        <a href="{{ route('support') }}" class="text-indigo-600 no-underline hover:underline">Get Help</a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
