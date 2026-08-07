<x-app-layout>
    <title>SmartSIM - {{ $title ?? 'Withdraw Funds' }}</title>
    
    {{-- Custom CSS --}}
    @push('styles')
    <style>
        /* Bootstrap class shims for compatibility with existing JS */
        .d-none { display: none !important; }
        .spinner-border {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            vertical-align: -0.125em;
            border: 0.15em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border .75s linear infinite;
        }
        .spinner-border-sm {
            width: .75rem;
            height: .75rem;
            border-width: .1em;
        }
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }

        /* Recent Recipients Scrollable Container custom scrollbar */
        #recentRecipientsBody {
            scrollbar-width: thin;
            scrollbar-color: rgba(66, 81, 124, 0.2) transparent;
        }
        #recentRecipientsBody::-webkit-scrollbar {
            width: 5px;
        }
        #recentRecipientsBody::-webkit-scrollbar-track {
            background: transparent;
        }
        #recentRecipientsBody::-webkit-scrollbar-thumb {
            background-color: rgba(66, 81, 124, 0.2);
            border-radius: 10px;
        }
        #recentRecipientsBody::-webkit-scrollbar-thumb:hover {
            background-color: rgba(66, 81, 124, 0.4);
        }
    </style>
    @endpush

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold font-display text-slate-900 flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-[#0056D2] border border-indigo-100/50 shadow-sm">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </div>
                    Secure Withdrawal
                </h1>
                <p class="text-sm text-slate-500 mt-1">Transfer funds directly to any Nigerian bank account instantly.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('wallet') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm transition-all duration-200 hover:-translate-y-0.5">
                    <i data-lucide="wallet" class="w-3.5 h-3.5 text-slate-400"></i>
                    My Wallet
                </a>
                <a href="{{ route('transfer') }}" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm transition-all duration-200 hover:-translate-y-0.5">
                    <i data-lucide="send" class="w-3.5 h-3.5 text-slate-400"></i>
                    P2P Transfer
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-emerald-800 flex items-start gap-3 shadow-sm animate-in fade-in duration-300">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-rose-50 border border-rose-100 rounded-2xl p-4 text-rose-800 flex items-start gap-3 shadow-sm animate-in fade-in duration-300">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        <!-- Grid Container -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            <!-- Left Card: Withdrawal Form -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden h-full flex flex-col justify-between">
                    
                    <div>
                        <!-- Card Header -->
                        <div class="bg-gradient-to-r from-[#0056D2] to-[#0049b8] px-6 py-5 border-b border-slate-100 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/10">
                                    <i data-lucide="banknote" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold font-display">New Payout</h3>
                                    <p class="text-xs text-slate-200 mt-0.5 font-medium">Verify recipient before submitting.</p>
                                </div>
                            </div>
                            <span class="inline-block text-[9px] font-extrabold text-[#0056D2] bg-white px-2.5 py-1 rounded-full uppercase tracking-wider">Settlement</span>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 space-y-5">
                            
                            {{-- Eligibility Banner --}}
                            @if($totalVolume < $eligibilityAmount)
                                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 text-amber-800 flex items-start gap-3 shadow-sm animate-in fade-in duration-300">
                                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"></i>
                                    <div class="text-xs leading-normal">
                                        <strong>You are not eligible for payouts yet</strong><br>
                                        Complete transactions to unlock transfer services.
                                    </div>
                                </div>
                            @else
                                <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-emerald-800 flex items-start gap-3 shadow-sm animate-in fade-in duration-300">
                                    <i data-lucide="shield-check" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                                    <div class="text-xs leading-normal">
                                        <strong>Account Verified</strong><br>
                                        Your account is active and qualified for instant bank settlements.
                                    </div>
                                </div>
                            @endif

                            {{-- Withdrawal Form --}}
                            <form id="withdrawForm" method="POST" action="{{ route('withdraw.process') }}" class="space-y-4">
                                @csrf

                                {{-- Bank Preview (shown after selection) --}}
                                <div id="bankPreviewWrapper" class="d-none">
                                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-center gap-3.5 shadow-inner">
                                        <div class="w-10 h-10 rounded-full bg-white border border-slate-150 flex items-center justify-center shrink-0 shadow-sm">
                                            <i data-lucide="bank" class="w-5 h-5 text-[#0056D2]" id="defaultBankIcon"></i>
                                            <img src="" id="selectedBankLogo" class="d-none w-6 h-6 object-contain">
                                        </div>
                                        <div class="min-w-0 flex-grow">
                                            <p id="previewBankName" class="font-bold text-xs text-slate-800 truncate">Select a Bank</p>
                                            <small id="previewAccountNo" class="text-[10px] text-slate-400 font-semibold tracking-wide">Enter details below</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Hidden native select for form submit & JS --}}
                                <select name="bankCode" id="bank_code" class="d-none" required>
                                    <option value="">Choose a bank...</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->bank_code }}"
                                            data-url="{{ $bank->bank_url ?? '' }}"
                                            data-bg="{{ $bank->bg_url ?? '' }}"
                                            data-name="{{ $bank->bank_name }}">
                                            {{ $bank->bank_name }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- Custom Bank Picker --}}
                                <div class="space-y-1.5" id="bankPickerWrapper">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Select Bank <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        {{-- Trigger --}}
                                        <div class="w-full flex items-center border border-slate-200 rounded-xl bg-white shadow-sm cursor-pointer select-none" id="bankPickerTrigger" role="button" tabindex="0">
                                            <div class="pl-3.5 pr-2 py-3.5 shrink-0 flex items-center text-slate-400 border-r border-slate-100" id="bankTriggerLogoWrap">
                                                <i data-lucide="bank" class="w-4 h-4" id="bankPickerIcon"></i>
                                                <img src="" id="bankPickerLogo" alt="" class="d-none w-5 h-5 object-contain">
                                            </div>
                                            <div class="flex-grow pl-3 pr-4 flex items-center justify-between text-xs font-semibold text-slate-700">
                                                <span id="bankPickerLabel" class="text-slate-400">Choose a bank...</span>
                                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" id="bankChevron"></i>
                                            </div>
                                        </div>

                                        {{-- Dropdown Panel --}}
                                        <div id="bankPickerDropdown" class="d-none absolute left-0 right-0 z-50 mt-1.5 bg-white border border-slate-150 rounded-2xl shadow-xl overflow-hidden animate-in fade-in duration-100">
                                            {{-- Search Input --}}
                                            <div class="p-2 border-b border-slate-100 bg-slate-50">
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <input type="text" id="bankSearchInput" class="w-full pl-8 pr-4 py-2 border border-slate-200 focus:border-[#0056D2] focus:ring-1 focus:ring-[#0056D2] rounded-lg text-xs font-semibold focus:outline-none transition-all shadow-inner bg-white" placeholder="Search bank name..." autocomplete="off">
                                                </div>
                                            </div>

                                            {{-- Option List --}}
                                            <ul class="list-none mb-0 max-h-56 overflow-y-auto divide-y divide-slate-50" id="bankPickerList">
                                                @foreach($banks as $bank)
                                                    <li class="bank-item flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors cursor-pointer"
                                                        data-code="{{ $bank->bank_code }}"
                                                        data-name="{{ $bank->bank_name }}"
                                                        data-url="{{ $bank->bank_url ?? '' }}"
                                                        data-bg="{{ $bank->bg_url ?? '' }}">
                                                        <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0">
                                                            @if($bank->bank_url)
                                                                <img src="{{ $bank->bank_url }}" alt="{{ $bank->bank_name }}" class="w-5 h-5 object-contain" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                                                                <i data-lucide="bank" class="w-3.5 h-3.5 text-[#0056D2]" style="display:none;"></i>
                                                            @else
                                                                <i data-lucide="bank" class="w-3.5 h-3.5 text-[#0056D2]"></i>
                                                            @endif
                                                        </div>
                                                        <span class="text-xs font-semibold text-slate-700">{{ $bank->bank_name }}</span>
                                                    </li>
                                                @endforeach
                                                <li class="px-4 py-6 text-center text-xs text-slate-400 d-none" id="bankNoResults">
                                                    No banks found
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Account Number --}}
                                <div class="space-y-1.5">
                                    <label for="account_no" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Account Number <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i data-lucide="credit-card" class="w-4 h-4"></i>
                                        </div>
                                        <input type="text" id="account_no" name="account_no"
                                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-[#0056D2] focus:ring-1 focus:ring-[#0056D2] text-xs font-semibold text-slate-700 bg-white focus:outline-none transition-all shadow-sm"
                                               placeholder="Enter 10-digit account number"
                                               maxlength="10"
                                               inputmode="numeric"
                                               required>
                                    </div>
                                    <div class="mt-1.5 min-h-[22px] flex flex-wrap gap-2 items-center">
                                        <div id="accountNameDisplay" class="text-[10px] font-bold"></div>
                                        <div id="accountErrorDisplay" class="text-[10px] font-bold text-rose-600"></div>
                                        <input type="hidden" name="account_name" id="account_name_hidden">
                                    </div>
                                </div>

                                {{-- Amount --}}
                                <div class="space-y-1.5">
                                    <div class="flex justify-between items-center text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        <label for="amount_display">Withdrawal Amount <span class="text-rose-500">*</span></label>
                                        <span class="text-slate-400 lowercase">Balance: 
                                            <strong class="text-emerald-600 font-extrabold uppercase">
                                                ₦{{ number_format(auth()->user()->wallet->balance ?? 0, 2) }}
                                            </strong>
                                        </span>
                                    </div>
                                    
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">
                                            ₦
                                        </div>
                                        <input type="text" id="amount_display"
                                               class="w-full pl-8 pr-4 py-3 rounded-xl border border-slate-200 focus:border-[#0056D2] focus:ring-1 focus:ring-[#0056D2] text-xs font-semibold text-slate-700 bg-white focus:outline-none transition-all shadow-sm"
                                               placeholder="0.00"
                                               required>
                                        <input type="hidden" id="amount" name="amount" required>
                                    </div>
                                    
                                    <div id="amount_in_words" class="text-[10px] font-bold text-[#0056D2] bg-[#0056D2]/5 px-3 py-2 rounded-xl mt-1.5 hidden leading-normal"></div>
                                    
                                    <div class="flex justify-between items-center text-[10px] text-slate-400 font-semibold pt-1">
                                        <span>Min: ₦100.00</span>
                                        <span>Daily Limit: ₦{{ number_format($user->limit, 2) }}</span>
                                    </div>
                                </div>

                                {{-- Warning --}}
                                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 text-amber-800 flex gap-3 shadow-sm">
                                    <i data-lucide="alert-circle" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"></i>
                                    <div class="text-[11px] leading-relaxed">
                                        <strong>Non-Reversible Transaction:</strong> Please verify all recipient details carefully before proceeding. SmartSIM cannot recover transfers to incorrect bank details.
                                    </div>
                                </div>

                                {{-- Submit Button --}}
                                <button type="button" id="proceedBtn" class="w-full py-3.5 px-6 bg-[#0056D2] hover:bg-[#003a8c] text-white font-bold text-xs rounded-xl shadow-md disabled:bg-slate-100 disabled:border-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed disabled:shadow-none transition-all duration-200 flex items-center justify-center gap-2"
                                        @if($totalVolume < $eligibilityAmount) disabled @endif>
                                    <i data-lucide="zap" class="w-4 h-4"></i>
                                    Authorize Payout
                                </button>

                                @if(auth()->user()->role === 'super_admin')
                                    <div class="text-center pt-2">
                                        <a href="{{ route('withdraw.syncBanks') }}" class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-slate-600 transition-colors bg-slate-50 border border-slate-200/60 px-3 py-1.5 rounded-full">
                                            <i data-lucide="refresh-cw" class="w-3 h-3"></i> Sync Bank Infrastructure
                                        </a>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- Footer Info -->
                    <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-center gap-1.5 text-[10px] text-slate-400 font-semibold">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#0056D2]/80"></i>
                        <span>PCI-DSS Secured Bank Gateway</span>
                    </div>

                </div>
            </div>

            <!-- Right Card: Recent Recipients -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden h-full flex flex-col justify-between">
                    
                    <div>
                        <!-- Header -->
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-[#0056D2] border border-indigo-100/50 shadow-inner">
                                <i data-lucide="history" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 font-display">Recent Recipients</h3>
                                <p class="text-xs text-slate-400 mt-0.5 font-medium">Tap a recipient to auto-fill the withdrawal details.</p>
                            </div>
                        </div>

                        <!-- Body (Scrollable Container) -->
                        <div class="p-6 overflow-y-auto max-h-[460px] space-y-3" id="recentRecipientsBody">
                            @if(isset($recentRecipients) && count($recentRecipients) > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($recentRecipients as $recipient)
                                        <div class="flex items-center gap-3 p-3 bg-white border border-slate-150 hover:border-[#0056D2]/30 hover:bg-[#0056D2]/5 rounded-2xl shadow-sm transition-all duration-200 cursor-pointer"
                                             onclick="selectRecentBank('{{ $recipient['bank_code'] }}', '{{ $recipient['account_no'] }}', '{{ $recipient['account_name'] }}')">

                                            <div class="w-10 h-10 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0">
                                                @if(!empty($recipient['bank_url']))
                                                    <img src="{{ $recipient['bank_url'] }}" alt="{{ $recipient['bank_name'] }}" class="w-6 h-6 object-contain" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                                                    <i data-lucide="landmark" class="w-4 h-4 text-[#0056D2]" style="display:none;"></i>
                                                @else
                                                    <i data-lucide="landmark" class="w-4 h-4 text-[#0056D2]"></i>
                                                @endif
                                            </div>

                                            <div class="flex-grow min-w-0">
                                                <span class="block text-xs font-bold text-slate-850 truncate leading-snug">{{ $recipient['account_name'] }}</span>
                                                <span class="block text-[10px] text-slate-400 font-medium truncate mt-0.5">
                                                    {{ $recipient['bank_name'] }} &bull; {{ $recipient['account_no'] }}
                                                </span>
                                            </div>

                                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 shrink-0"></i>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-12 text-slate-400 max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto text-slate-300">
                                        <i data-lucide="users-2" class="w-6 h-6"></i>
                                    </div>
                                    <h5 class="font-bold text-slate-800 text-xs">No Recent Payouts</h5>
                                    <p class="text-[11px] text-slate-400 leading-normal px-6">
                                        Your trusted recipients will appear here automatically after your first successful withdrawal.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-center text-[10px] text-slate-400 font-semibold gap-1">
                        <span>Protected by Multi-Factor Authentication</span>
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- PIN Modal --}}
    @include('pages.pin')

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ── References ───────────────────────────────── */
        const withdrawalFee     = {{ $withdrawalFee ?? 0 }};
        const accountNoInput    = document.getElementById('account_no');
        const bankCodeSelect    = document.getElementById('bank_code');
        const accountNameDisp   = document.getElementById('accountNameDisplay');
        const accountErrorDisp  = document.getElementById('accountErrorDisplay');
        const accountNameHidden = document.getElementById('account_name_hidden');
        const proceedBtn        = document.getElementById('proceedBtn');
        const amountInput       = document.getElementById('amount');
        const amountDisplay     = document.getElementById('amount_display');
        const amountInWordsDisp = document.getElementById('amount_in_words');

        /* ── Amount Input Styling Config ─────────────── */
        if (amountDisplay) {
            amountDisplay.addEventListener('input', function() {
                let cursorPosition = this.selectionStart;
                let originalValue = this.value;
                let rawValue = originalValue.replace(/,/g, '');

                if (rawValue === '') {
                    amountInput.value = '';
                    if (amountInWordsDisp) {
                        amountInWordsDisp.textContent = '';
                        amountInWordsDisp.classList.add('hidden');
                    }
                    return;
                }

                if (isNaN(rawValue)) {
                    this.value = rawValue.slice(0, -1);
                    return;
                }

                amountInput.value = rawValue;

                // Amount to English words update
                if (amountInWordsDisp) {
                    let w = numberToWords(rawValue);
                    if (w) {
                        amountInWordsDisp.textContent = w;
                        amountInWordsDisp.classList.remove('hidden');
                    } else {
                        amountInWordsDisp.textContent = '';
                        amountInWordsDisp.classList.add('hidden');
                    }
                }

                // Format live with commas
                let parts = rawValue.split('.');
                let integerPart = parts[0];
                let decimalPart = parts[1];

                let formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                let formattedValue = formattedInteger;
                if (decimalPart !== undefined) {
                    formattedValue += '.' + decimalPart;
                }

                this.value = formattedValue;

                // Reset cursor position
                let lengthDiff = formattedValue.length - originalValue.length;
                let newSelection = cursorPosition + lengthDiff;
                newSelection = Math.max(0, Math.min(newSelection, formattedValue.length));
                this.setSelectionRange(newSelection, newSelection);
            });

            // Format to standard 2 decimal places on blur
            amountDisplay.addEventListener('blur', function() {
                if (amountInput.value) {
                    let val = parseFloat(amountInput.value);
                    if (!isNaN(val)) {
                        this.value = val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        amountInput.value = val.toFixed(2);
                    }
                }
            });

            // Strip trailing .00 on focus for easier editing of whole numbers
            amountDisplay.addEventListener('focus', function() {
                if (amountInput.value) {
                    let val = parseFloat(amountInput.value);
                    if (!isNaN(val)) {
                        if (val % 1 === 0) {
                            this.value = val.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        }
                    }
                }
            });
        }

        // Helper function: Convert number to English words
        function numberToWords(num) {
            if (isNaN(num) || num === '') return '';
            
            let n = parseFloat(num);
            if (n === 0) return 'Zero Naira Only';
            
            const a = [
                '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
            ];
            const b = [
                '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
            ];
            const g = [
                '', 'Thousand', 'Million', 'Billion', 'Trillion'
            ];

            function chunk(n) {
                if (n === 0) return '';
                let str = '';
                if (n >= 100) {
                    str += a[Math.floor(n / 100)] + ' Hundred ';
                    n %= 100;
                }
                if (n >= 20) {
                    str += b[Math.floor(n / 10)] + ' ';
                    if (n % 10 > 0) {
                        str += a[n % 10] + ' ';
                    }
                } else if (n > 0) {
                    str += a[n] + ' ';
                }
                return str;
            }

            let integerPart = Math.floor(n);
            let decimalPart = Math.round((n - integerPart) * 100);

            let words = '';
            
            if (integerPart === 0) {
                words = 'Zero ';
            } else {
                let parts = [];
                let groupIdx = 0;
                while (integerPart > 0) {
                    let rem = integerPart % 1000;
                    if (rem > 0) {
                        let cStr = chunk(rem);
                        let gStr = g[groupIdx];
                        parts.unshift(cStr + (gStr ? gStr + ' ' : ''));
                    }
                    integerPart = Math.floor(integerPart / 1000);
                    groupIdx++;
                }
                words = parts.join('').trim() + ' ';
            }

            words += 'Naira';

            if (decimalPart > 0) {
                words += ' and ' + chunk(decimalPart).trim() + ' Kobo';
            }

            return words.replace(/\s+/g, ' ').trim() + ' Only';
        }

        // Bank preview
        const bankPreviewWrapper = document.getElementById('bankPreviewWrapper');
        const previewBankName    = document.getElementById('previewBankName');
        const previewAcctNo      = document.getElementById('previewAccountNo');
        const selBankLogo        = document.getElementById('selectedBankLogo');
        const defBankIcon        = document.getElementById('defaultBankIcon');

        // Modal
        const confirmationStep = document.getElementById('confirmationStep');
        const pinStep          = document.getElementById('pinStep');
        const btnGoToPin       = document.getElementById('btnGoToPin');
        const btnBackToConfirm = document.getElementById('btnBackToConfirm');
        const modalTitle       = document.getElementById('modalTitle');
        const modalSubtitle    = document.getElementById('modalSubtitle');

        let pinModal;
        try { pinModal = new bootstrap.Modal(document.getElementById('pinModal')); }
        catch (e) { console.error('Modal init failed:', e); }

        let verificationTimeout;

        /* ── Bank Preview ─────────────────────────────── */
        function updateBankPreview() {
            const opt    = bankCodeSelect.options[bankCodeSelect.selectedIndex];
            const name   = opt ? opt.text.trim() : '';
            const url    = opt ? opt.getAttribute('data-url') : null;
            const bgUrl  = opt ? opt.getAttribute('data-bg')  : null;
            const acctNo = accountNoInput.value;

            if (bankCodeSelect.value) {
                bankPreviewWrapper.style.display = 'block';
                previewBankName.textContent = name;
                previewAcctNo.textContent   = acctNo || 'Enter account number';

                if (url) {
                    selBankLogo.src = url;
                    selBankLogo.classList.remove('d-none');
                    defBankIcon.classList.add('d-none');
                } else {
                    selBankLogo.classList.add('d-none');
                    defBankIcon.classList.remove('d-none');
                }
            } else {
                bankPreviewWrapper.style.display = 'none';
            }
        }

        /* ── Account Verification ─────────────────────── */
        function performVerification() {
            const bankCode = bankCodeSelect.value;
            const acctNo   = accountNoInput.value;
            updateBankPreview();

            if (bankCode && acctNo.length === 10) {
                accountNameDisp.innerHTML  = '<span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-500 bg-slate-50 border border-slate-200 px-3 py-1 rounded-full"><span class="w-2.5 h-2.5 border border-slate-400 border-t-transparent rounded-full animate-spin"></span> Verifying...</span>';
                accountErrorDisp.innerHTML = '';

                fetch("{{ route('withdraw.verifyAccount') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ bankCode: bankCode, account_no: acctNo })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        accountNameDisp.innerHTML  = `<span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-full shadow-sm"><i data-lucide="check-circle" class="w-3.5 h-3.5 inline-block"></i> ${data.account_name}</span>`;
                        accountNameHidden.value    = data.account_name;
                        accountErrorDisp.innerHTML = '';
                    } else {
                        accountNameDisp.innerHTML  = '';
                        accountErrorDisp.innerHTML = `<span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 bg-rose-50 border border-rose-100 px-3 py-1 rounded-full shadow-sm"><i data-lucide="alert-circle" class="w-3.5 h-3.5 inline-block"></i> ${data.message}</span>`;
                        accountNameHidden.value    = '';
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                })
                .catch(() => {
                    accountNameDisp.innerHTML  = '';
                    accountErrorDisp.innerHTML = '<span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-100 px-3 py-1 rounded-full shadow-sm"><i data-lucide="wifi-off" class="w-3.5 h-3.5 inline-block"></i> Connection failed</span>';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            }
        }

        accountNoInput.addEventListener('input', () => {
            clearTimeout(verificationTimeout);
            updateBankPreview();
            if (accountNoInput.value.length === 10) {
                verificationTimeout = setTimeout(performVerification, 500);
            } else {
                accountNameDisp.innerHTML  = '';
                accountErrorDisp.innerHTML = '';
                accountNameHidden.value    = '';
            }
        });

        bankCodeSelect.addEventListener('change', performVerification);

        /* ══ Custom Bank Picker ═══════════════════════════════════════ */
        const bankPickerTrigger  = document.getElementById('bankPickerTrigger');
        const bankPickerDropdown = document.getElementById('bankPickerDropdown');
        const bankSearchInput    = document.getElementById('bankSearchInput');
        const bankPickerLabel    = document.getElementById('bankPickerLabel');
        const bankPickerLogo     = document.getElementById('bankPickerLogo');
        const bankPickerIcon     = document.getElementById('bankPickerIcon');
        const bankChevron        = document.getElementById('bankChevron');
        const bankItems          = document.querySelectorAll('.bank-item');
        const bankNoResults      = document.getElementById('bankNoResults');

        function openBankPicker() {
            bankPickerDropdown.classList.remove('d-none');
            bankChevron.style.transform = 'rotate(180deg)';
            bankSearchInput.value = '';
            bankItems.forEach(el => el.classList.remove('d-none'));
            if (bankNoResults) bankNoResults.classList.add('d-none');
            setTimeout(() => bankSearchInput.focus(), 60);
        }
        function closeBankPicker() {
            bankPickerDropdown.classList.add('d-none');
            bankChevron.style.transform = '';
        }
        function applyBankSelection(code, name, url) {
            bankCodeSelect.value = code;
            bankCodeSelect.dispatchEvent(new Event('change'));

            bankPickerLabel.textContent = name;
            bankPickerLabel.classList.remove('text-slate-400');
            bankPickerLabel.classList.add('text-slate-800', 'font-bold');

            bankItems.forEach(el => {
                el.style.background = el.getAttribute('data-code') === code ? 'rgba(66, 81, 124, 0.08)' : '';
            });

            if (url) {
                bankPickerLogo.src = url;
                bankPickerLogo.classList.remove('d-none');
                bankPickerIcon.classList.add('d-none');
            } else {
                bankPickerLogo.classList.add('d-none');
                bankPickerIcon.classList.remove('d-none');
            }
            closeBankPicker();
        }

        bankPickerTrigger.addEventListener('click', e => {
            e.stopPropagation();
            bankPickerDropdown.classList.contains('d-none') ? openBankPicker() : closeBankPicker();
        });

        bankSearchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            let visible = 0;
            bankItems.forEach(item => {
                const match = item.getAttribute('data-name').toLowerCase().includes(q);
                item.classList.toggle('d-none', !match);
                if (match) visible++;
            });
            bankNoResults.classList.toggle('d-none', visible > 0);
        });

        bankItems.forEach(item => {
            item.addEventListener('click', () => applyBankSelection(
                item.getAttribute('data-code'),
                item.getAttribute('data-name'),
                item.getAttribute('data-url')
            ));
            item.addEventListener('mouseenter', () => {
                if (item.getAttribute('data-code') !== bankCodeSelect.value) {
                    item.style.background = 'rgba(66, 81, 124, 0.04)';
                }
            });
            item.addEventListener('mouseleave', () => {
                if (item.getAttribute('data-code') !== bankCodeSelect.value) {
                    item.style.background = '';
                }
            });
        });

        document.addEventListener('click', e => {
            if (!document.getElementById('bankPickerWrapper')?.contains(e.target)) {
                closeBankPicker();
            }
        });
        /* ══ End Bank Picker ══════════════════════════════════════════ */

        /* ── Quick Select Recent Recipient ────────────── */
        window.selectRecentBank = function (bankCode, accountNo, accountName) {
            const opt = bankCodeSelect.querySelector(`option[value="${bankCode}"]`);
            if (opt) {
                applyBankSelection(bankCode, opt.getAttribute('data-name') || opt.text.trim(), opt.getAttribute('data-url') || '');
            }
            accountNoInput.value = accountNo;
            accountNoInput.classList.add('border-emerald-500');
            setTimeout(() => accountNoInput.classList.remove('border-emerald-500'), 2000);

            accountNameDisp.innerHTML  = `<span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-full shadow-sm"><i data-lucide="check-circle" class="w-3.5 h-3.5 inline-block"></i> ${accountName}</span>`;
            accountNameHidden.value    = accountName;
            accountErrorDisp.innerHTML = '';
            updateBankPreview();

            if (typeof lucide !== 'undefined') lucide.createIcons();

            document.getElementById('amount_display').focus();
            document.getElementById('withdrawForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
        };

        /* ── Proceed Button → Open Modal ──────────────── */
        proceedBtn.addEventListener('click', function () {
            const amount      = amountInput.value;
            const bankName    = bankCodeSelect.options[bankCodeSelect.selectedIndex]?.text || '';
            const accountNo   = accountNoInput.value;
            const accountName = accountNameHidden.value;

            if (!amount || parseFloat(amount) < 100) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Amount',
                    text: 'Please enter a valid amount (Min ₦100).',
                    confirmButtonColor: '#0056D2',
                });
                return;
            }
            if (!accountName) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Account Not Verified',
                    text: 'Please wait for account name verification.',
                    confirmButtonColor: '#0056D2',
                });
                return;
            }

            const totalAmount = parseFloat(amount) + withdrawalFee;

            document.getElementById('confirmAccountName').textContent = accountName;
            document.getElementById('confirmBankName').textContent    = bankName;
            document.getElementById('confirmAccountNo').textContent   = accountNo;
            document.getElementById('confirmAmount').textContent      = '₦' + totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2 });

            confirmationStep.classList.remove('hidden');
            pinStep.classList.add('hidden');
            modalTitle.textContent    = 'Confirm Transaction';
            modalSubtitle.textContent = 'Please review details carefully';

            (pinModal || new bootstrap.Modal(document.getElementById('pinModal'))).show();
        });

        /* ── Modal Step Navigation ────────────────────── */
        btnGoToPin.addEventListener('click', () => {
            confirmationStep.classList.add('hidden');
            pinStep.classList.remove('hidden');
            modalTitle.textContent    = 'Authorize Payout';
            modalSubtitle.textContent = 'Step 2 of 2 — Security PIN';
            document.getElementById('pinInput').focus();
        });

        btnBackToConfirm?.addEventListener('click', () => {
            pinStep.classList.add('hidden');
            confirmationStep.classList.remove('hidden');
            modalTitle.textContent    = 'Confirm Transaction';
            modalSubtitle.textContent = 'Please review details carefully';
        });

        /* ── PIN Submit ───────────────────────────────── */
        document.getElementById('confirmPinBtn').addEventListener('click', function () {
            const confirmBtn   = this;
            const loader       = document.getElementById('pinLoader');
            const confirmText  = document.getElementById('confirmPinText');
            const pinError     = document.getElementById('pinError');
            const pinErrorText = document.getElementById('pinErrorText');
            const pin          = document.getElementById('pinInput').value.trim();

            function setPinError(msg) {
                if (pinErrorText) pinErrorText.textContent = msg;
                pinError?.classList.remove('hidden');
            }

            if (!pin || pin.length !== 5) { setPinError('Please enter your 5-digit PIN.'); return; }

            confirmBtn.disabled = true;
            loader.classList.remove('hidden');
            confirmText.textContent = 'Verifying...';
            pinError?.classList.add('hidden');

            fetch("{{ route('verify.pin') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ pin })
            })
            .then(r => r.json())
            .then(data => {
                if (data.valid) {
                    const form = document.getElementById('withdrawForm');
                    const h = document.createElement('input');
                    h.type = 'hidden'; h.name = 'pin'; h.value = pin;
                    form.appendChild(h);
                    form.submit();
                } else {
                    setPinError('Incorrect PIN. Please try again.');
                    confirmBtn.disabled = false;
                    loader.classList.add('hidden');
                    confirmText.textContent = 'Authorize Now';
                    document.getElementById('pinInput').value = '';
                }
            })
            .catch(() => {
                setPinError('Connection error. Please try again.');
                confirmBtn.disabled = false;
                loader.classList.add('hidden');
                confirmText.textContent = 'Authorize Now';
            });
        });

    });
    </script>
</x-app-layout>