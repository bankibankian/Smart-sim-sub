<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SmartSIM') }}</title>

        <!-- Description -->
        <meta name="description" content="SmartSIM - Empowering Businesses Through Smart Connectivity. Purchase bulk SIM cards wholesale, manage allocations, and get massive welcome data bonuses for the public.">
        <meta name="keywords" content="telecom, SIM card, wholesale SIM, sell SIMs, data welcome bonus, mobile network, SmartSIMSub">

        <!-- Favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/logo/favicon.png') }}" type="image/png">


        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Lucide Icons CDN -->
        <script src="https://unpkg.com/lucide@latest"></script>
    </head>
    <body class="font-sans text-gray-900 antialiased h-full bg-[#f8fafc]">
        <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12 overflow-x-hidden">
            <!-- Left Banner (Col-5, Desktop Only) -->
            <div class="hidden lg:flex lg:col-span-5 relative bg-gradient-to-br from-slate-950 via-[#1e293b] to-[#42517c] p-12 flex-col justify-between overflow-hidden">
                <!-- Background Glowing Blobs -->
                <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-[#2EBE59]/10 rounded-full blur-[80px] -mr-40 -mt-40"></div>
                <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-[#42517c]/10 rounded-full blur-[80px] -ml-40 -mb-40"></div>
                
                <!-- Logo / Header -->
                <div class="relative z-10">
                    <a href="/" class="flex items-center gap-3">
                        <img src="{{ asset('assets/images/logo/logo1.png') }}" alt="SmartSIM Logo" class="h-10 w-auto brightness-0 invert">
                    </a>
                </div>

                <!-- Center Content: Slogan and Platform features -->
                <div class="relative z-10 my-auto">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#2EBE59]/10 text-[#2EBE59] border border-[#2EBE59]/20 uppercase tracking-wider mb-6 font-display">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2EBE59] animate-pulse"></span>
                        SmartSIM Portal
                    </span>
                    <h2 class="text-3xl lg:text-4xl font-extrabold text-white tracking-tight leading-tight font-display mb-6">
                        Empowering Businesses Through <span class="bg-gradient-to-r from-[#42517c] to-[#2EBE59] bg-clip-text text-transparent">Smart Connectivity</span>
                    </h2>
                    
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="p-2 bg-white/5 rounded-lg border border-white/10 text-[#42517c]">
                                <i data-lucide="tag" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm font-display">Cheaper Data & Airtime</h4>
                                <p class="text-slate-400 text-xs mt-1">Access the cheapest data and call rates in Nigeria instantly.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="p-2 bg-white/5 rounded-lg border border-white/10 text-[#2EBE59]">
                                <i data-lucide="zap" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm font-display">Instant Activation</h4>
                                <p class="text-slate-400 text-xs mt-1">SIM serial validation takes seconds to process live.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="p-2 bg-white/5 rounded-lg border border-white/10 text-amber-500">
                                <i data-lucide="gift" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm font-display">Welcome Data Bonus</h4>
                                <p class="text-slate-400 text-xs mt-1">Public customers enjoy up to 100GB Free Data + ₦15,000 bonus.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer of Left Panel -->
                <div class="relative z-10 text-xs text-slate-500 font-display flex items-center justify-between">
                    <span>© {{ date('Y') }} SmartSIM. All rights reserved.</span>
                    <a href="/support" class="hover:text-white transition">Support</a>
                </div>
            </div>

            <!-- Right Panel (Col-7, Mobile & Desktop Form) -->
            <div class="col-span-1 lg:col-span-7 flex flex-col justify-center py-12 px-6 sm:px-12 lg:px-20 bg-slate-50 relative">
                <!-- Mobile Logo Header -->
                <div class="lg:hidden flex justify-center mb-8">
                    <a href="/">
                        <img src="{{ asset('assets/images/logo/logo.png') }}" alt="SmartSIM Logo" class="h-12 w-auto">
                    </a>
                </div>

                <!-- Main Card Container -->
                <div class="mx-auto w-full max-w-md bg-white p-8 sm:p-10 rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/50">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <script>
            // Initialize Lucide Icons
            lucide.createIcons();

            // Form submission processing state visual handler
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        // Prevent double submission
                        if (submitBtn.classList.contains('is-processing')) {
                            e.preventDefault();
                            return;
                        }
                        
                        submitBtn.classList.add('is-processing');
                        submitBtn.style.opacity = '0.8';
                        
                        // Look for span and icon to replace
                        const textSpan = submitBtn.querySelector('span');
                        const icon = submitBtn.querySelector('[data-lucide]');
                        
                        if (textSpan) {
                            textSpan.textContent = 'Processing...';
                        } else {
                            submitBtn.textContent = 'Processing...';
                        }
                        
                        if (icon) {
                            icon.setAttribute('data-lucide', 'loader-2');
                            icon.classList.add('animate-spin');
                        } else {
                            // Create icon if not present
                            const newIcon = document.createElement('i');
                            newIcon.setAttribute('data-lucide', 'loader-2');
                            newIcon.className = 'w-4 h-4 animate-spin ml-2 inline-block';
                            submitBtn.appendChild(newIcon);
                        }
                        
                        lucide.createIcons();
                    }
                });
            });
        </script>
    </body>
</html>
