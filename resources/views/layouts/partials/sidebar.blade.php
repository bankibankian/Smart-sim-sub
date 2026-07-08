<!-- Sidebar Overlay (Mobile) -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false" 
     class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden" 
     style="display: none;">
</div>

<!-- Sidebar Container -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
       class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 border-r border-slate-800/80 flex flex-col transform lg:translate-x-0 lg:static lg:h-screen transition-transform duration-300 ease-in-out">
    
    <!-- Brand Header -->
    <div class="px-5 py-4 border-b border-slate-800/80">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center p-1 shadow-md shadow-indigo-950/20">
                <img src="{{ asset('assets/images/logo/favicon.png') }}" alt="SmartSIM Logo" class="w-5.5 h-5.5 object-contain">
            </div>
            <div>
                <span class="text-base font-bold text-white font-display tracking-tight">SmartSIM</span>
                <span class="block text-[8px] font-semibold text-[#55699e] uppercase tracking-wider -mt-0.5">
                    {{ strtoupper(str_replace('_', ' ', auth()->user()->role ?? 'user')) }} PANEL
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-grow px-4 py-6 space-y-1.5 overflow-y-auto">
        <!-- Dashboard Link -->
        <a href="{{ route('dashboard') }}" 
           class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('dashboard') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
            @if(request()->routeIs('dashboard'))
                <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
            @endif
            <i data-lucide="layout-dashboard" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('dashboard') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
            <span>{{ __('Dashboard') }}</span>
        </a>

        <!-- Wallet Dropdown Sub-Selection -->
        <div x-data="{ open: {{ request()->routeIs('wallet', 'transfer', 'withdraw') ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="open = !open" 
                    class="w-full group flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display focus:outline-none {{ request()->routeIs('wallet', 'transfer', 'withdraw') ? 'bg-[#42517c]/5 text-slate-200 font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="wallet" class="w-5 h-5 {{ request()->routeIs('wallet', 'transfer', 'withdraw') ? 'text-[#55699e]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>Wallet</span>
                </div>
                <i data-lucide="chevron-down" 
                   class="w-4 h-4 text-slate-500 transition-transform duration-200"
                   :class="open ? 'rotate-180' : ''"></i>
            </button>

            <!-- Sub Selection Links -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="pl-4 space-y-1"
                 style="display: none;">
                
                <!-- Wallet Link -->
                <a href="{{ route('wallet') }}" 
                   class="group flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('wallet') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                    @if(request()->routeIs('wallet'))
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="wallet" class="w-4 h-4 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('wallet') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>{{ __('My Wallet') }}</span>
                </a>

                <!-- P2P Transfer Link -->
                <a href="{{ route('transfer') }}" 
                   class="group flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('transfer') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                    @if(request()->routeIs('transfer'))
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="send" class="w-4 h-4 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('transfer') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>{{ __('P2P Transfer') }}</span>
                </a>

                <!-- Secure Payout Link -->
                <a href="{{ route('withdraw') }}" 
                   class="group flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('withdraw') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                    @if(request()->routeIs('withdraw'))
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="banknote" class="w-4 h-4 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('withdraw') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>{{ __('Secure Withdrawal') }}</span>
                </a>
            </div>
        </div>

        <!-- Buy Airtime Link -->
        <a href="{{ route('airtime') }}" 
           class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('airtime') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
            @if(request()->routeIs('airtime'))
                <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
            @endif
            <i data-lucide="phone" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('airtime') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
            <span>{{ __('Buy Airtime') }}</span>
        </a>

        <!-- Buy Data Link -->
        <a href="{{ route('buy-sme-data') }}" 
           class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('buy-sme-data*') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
            @if(request()->routeIs('buy-sme-data*'))
                <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
            @endif
            <i data-lucide="wifi" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('buy-sme-data*') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
            <span>{{ __('Buy Data') }}</span>
        </a>

        <!-- SIM Services Link -->
        <a href="{{ route('sims.index') }}" 
           class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('sims.*') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
            @if(request()->routeIs('sims.*'))
                <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
            @endif
            <i data-lucide="cpu" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('sims.*') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
            <span>{{ __('SIM Services') }}</span>
        </a>

        <!-- Verification Dropdown -->
        <div x-data="{ open: {{ request()->routeIs('bvn.verification.index', 'nin.verification.index', 'nin.demo.index', 'nin.phone.index') ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="open = !open" 
                    class="w-full group flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display focus:outline-none {{ request()->routeIs('bvn.verification.index', 'nin.verification.index', 'nin.demo.index', 'nin.phone.index') ? 'bg-[#42517c]/5 text-slate-200 font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="shield-check" class="w-5 h-5 {{ request()->routeIs('bvn.verification.index', 'nin.verification.index', 'nin.demo.index', 'nin.phone.index') ? 'text-[#55699e]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>Verification</span>
                </div>
                <i data-lucide="chevron-down" 
                   class="w-4 h-4 text-slate-500 transition-transform duration-200"
                   :class="open ? 'rotate-180' : ''"></i>
            </button>

            <!-- Sub Selection Links -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="pl-4 space-y-1"
                 style="display: none;">
                
                <!-- BVN Verification -->
                <a href="{{ route('bvn.verification.index') }}" 
                   class="group flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('bvn.verification.index') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                    @if(request()->routeIs('bvn.verification.index'))
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="fingerprint" class="w-4 h-4 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('bvn.verification.index') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>BVN Verification</span>
                </a>

                <!-- NIN Verification -->
                <a href="{{ route('nin.verification.index') }}" 
                   class="group flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('nin.verification.index') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                    @if(request()->routeIs('nin.verification.index'))
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="file-check-2" class="w-4 h-4 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('nin.verification.index') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>NIN Verification</span>
                </a>

                <!-- NIN Demo Verification -->
                <a href="{{ route('nin.demo.index') }}" 
                   class="group flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('nin.demo.index') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                    @if(request()->routeIs('nin.demo.index'))
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="users" class="w-4 h-4 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('nin.demo.index') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>NIN Demo</span>
                </a>

                <!-- NIN Phone Verification -->
                <a href="{{ route('nin.phone.index') }}" 
                   class="group flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('nin.phone.index') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                    @if(request()->routeIs('nin.phone.index'))
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="phone" class="w-4 h-4 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('nin.phone.index') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>NIN Phone</span>
                </a>
            </div>
        </div>

        <!-- Transactions Link -->
        <a href="{{ route('transactions') }}" 
           class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('transactions') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
            @if(request()->routeIs('transactions'))
                <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
            @endif
            <i data-lucide="history" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('transactions') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
            <span>{{ __('Transaction') }}</span>
        </a>


         <!-- support Link -->
        <a href="{{ route('support') }}" 
           class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('support') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
            @if(request()->routeIs('support'))
                <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
            @endif
            <i data-lucide="help-circle" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('support') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
            <span>{{ __('Support') }}</span>
        </a>

        <!-- Profile Link -->
        <a href="{{ route('profile.edit') }}" 
           class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('profile.edit') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
            @if(request()->routeIs('profile.edit'))
                <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
            @endif
            <i data-lucide="user-cog" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('profile.edit') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
            <span>{{ __('Profile Settings') }}</span>
        </a>

        @if (auth()->user() && auth()->user()->role === 'super_admin')
            <!-- Admin Management Section -->
            <div class="pt-4 mt-4 border-t border-slate-800/80">
                <span class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Administration</span>
                
                <!-- Users Dropdown Sub-Selection -->
                <div x-data="{ open: {{ request()->routeIs('admin.manage.users*', 'admin.manage.upgrades*', 'admin.manage.access*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open" 
                            class="w-full group flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display focus:outline-none {{ request()->routeIs('admin.manage.users*', 'admin.manage.upgrades*', 'admin.manage.access*') ? 'bg-[#42517c]/5 text-slate-200 font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="users" class="w-5 h-5 {{ request()->routeIs('admin.manage.users*', 'admin.manage.upgrades*', 'admin.manage.access*') ? 'text-[#55699e]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                            <span>Users</span>
                        </div>
                        <i data-lucide="chevron-down" 
                           class="w-4 h-4 text-slate-500 transition-transform duration-200"
                           :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- Sub Selection Links -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="pl-4 space-y-1"
                         style="display: none;">
                        
                        <!-- Manage Users Link -->
                        <a href="{{ route('admin.manage.users') }}" 
                           class="group flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('admin.manage.users*') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                            @if(request()->routeIs('admin.manage.users*'))
                                <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-[#42517c] rounded-r-full"></div>
                            @endif
                            <i data-lucide="users" class="w-4 h-4 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('admin.manage.users*') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                            <span>Manage Users</span>
                        </a>

                        <!-- Manage Upgrades Link -->
                        <a href="{{ route('admin.manage.upgrades') }}" 
                           class="group flex items-xl gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('admin.manage.upgrades*') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                            @if(request()->routeIs('admin.manage.upgrades*'))
                                <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-[#42517c] rounded-r-full"></div>
                            @endif
                            <i data-lucide="arrow-up-circle" class="w-4 h-4 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('admin.manage.upgrades*') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                            <span>Manage Upgrades</span>
                        </a>

                        <!-- Manage Access Link -->
                        <a href="{{ route('admin.manage.access') }}" 
                           class="group flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('admin.manage.access*') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }}">
                            @if(request()->routeIs('admin.manage.access*'))
                                <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-[#42517c] rounded-r-full"></div>
                            @endif
                            <i data-lucide="shield-check" class="w-4 h-4 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('admin.manage.access*') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                            <span>Manage Access</span>
                        </a>
                    </div>
                </div>

                <!-- Services Management Link -->
                <a href="{{ route('admin.services.index') }}" 
                   class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('admin.services*') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }} mt-2">
                    @if(request()->routeIs('admin.services*'))
                        <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="server" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('admin.services*') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>Services Pricing</span>
                </a>

                <!-- SME Data Plans Link -->
                <a href="{{ route('admin.sme-plans.index') }}" 
                   class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('admin.sme-plans*') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }} mt-2">
                    @if(request()->routeIs('admin.sme-plans*'))
                        <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="wifi" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('admin.sme-plans*') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>SME Data Plans</span>
                </a>

                <!-- SIM Plans Management Link -->
                <a href="{{ route('admin.sim-plan.index') }}" 
                   class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('admin.sim-plan*') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }} mt-2">
                    @if(request()->routeIs('admin.sim-plan*'))
                        <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="settings" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('admin.sim-plan*') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>SIM Plans</span>
                </a>

                <!-- System Transactions Link -->
                <a href="{{ route('admin.transactions') }}" 
                   class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('admin.transactions*') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }} mt-2">
                    @if(request()->routeIs('admin.transactions*'))
                        <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="receipt" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('admin.transactions*') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>All Transactions</span>
                </a>

                <!-- Admin Support Link -->
                <a href="{{ route('admin.manage.support.index') }}" 
                   class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 font-display relative {{ request()->routeIs('admin.manage.support*') ? 'bg-[#42517c]/10 text-white font-bold' : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200' }} mt-2">
                    @if(request()->routeIs('admin.manage.support*'))
                        <div class="absolute left-0 top-3 bottom-3 w-1 bg-[#42517c] rounded-r-full"></div>
                    @endif
                    <i data-lucide="message-square" class="w-5 h-5 transition-transform duration-200 group-hover:scale-105 {{ request()->routeIs('admin.manage.support*') ? 'text-[#42517c]' : 'text-slate-400 group-hover:text-slate-300' }}"></i>
                    <span>Admin Support</span>
                </a>
            </div>
        @endif

        <!-- Logout Link -->
        <form method="POST" action="{{ route('logout') }}" class="m-0 mt-4 pt-4 border-t border-slate-800/80">
            @csrf
            <button type="submit"
               class="w-full group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-all duration-200 font-display border-0 cursor-pointer bg-transparent text-left focus:outline-none">
                <i data-lucide="log-out" class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-0.5"></i>
                <span>{{ __('Log Out') }}</span>
            </button>
        </form>
    </nav>
</aside>
