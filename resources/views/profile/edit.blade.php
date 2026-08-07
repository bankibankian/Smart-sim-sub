<x-app-layout>
    <!-- Page Header Hero Card -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
            <!-- Decorative Accent Gradients -->
            <div class="absolute -top-24 -left-24 w-48 h-48 bg-[#0056D2]/5 rounded-full blur-3xl"></div>
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl"></div>

            <div class="flex items-center gap-5 relative z-10">
                <div class="relative">
                    @if ($user->profile_photo)
                        <img src="{{ asset($user->profile_photo) }}" 
                             class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-4 border-slate-50 shadow-md" 
                             alt="Avatar">
                    @else
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-[#0056D2]/10 text-[#0056D2] flex items-center justify-center font-bold text-3xl font-display border-4 border-slate-50 shadow-md">
                            {{ strtoupper(substr($user->first_name ?? $user->email, 0, 1)) }}
                        </div>
                    @endif
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 rounded-full border-4 border-white flex items-center justify-center shadow">
                        <span class="block w-2 h-2 bg-emerald-100 rounded-full animate-ping"></span>
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-800 font-display">
                        {{ $user->first_name }} {{ $user->last_name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-3 mt-1.5">
                        <span class="px-3 py-0.5 text-xs font-bold bg-[#0056D2]/10 text-[#0056D2] rounded-full uppercase tracking-wider">
                            {{ $user->role }}
                        </span>
                        <span class="text-slate-300 text-xs">•</span>
                        <span class="px-3 py-0.5 text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full uppercase tracking-wider flex items-center gap-1">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            Tier {{ $user->account_tier }} Verified
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-4 relative z-10">
                <div class="bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl text-center md:text-right min-w-[150px]">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Wallet Balance</span>
                    <span class="block text-xl font-extrabold text-[#0056D2] font-display">
                        ₦{{ number_format($user->wallet->balance ?? 0.00, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation and View Panel -->
    <div x-data="{ 
        activeTab: '{{ $errors->updatePassword->any() || $errors->updatePin->any() ? 'security' : ($errors->any() ? 'profile' : 'profile') }}'
    }" class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
        
        <!-- Sidebar Navigation Options -->
        <div class="lg:col-span-1 space-y-2 bg-white p-4 rounded-3xl border border-slate-100 shadow-sm">
            <h3 class="px-3 mb-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Settings Menu</h3>
            
            <button @click="activeTab = 'profile'" 
                    :class="activeTab === 'profile' ? 'bg-[#0056D2] text-white shadow-lg shadow-[#0056D2]/10' : 'bg-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900'" 
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span>Personal Profile</span>
            </button>
            
            <button @click="activeTab = 'security'" 
                    :class="activeTab === 'security' ? 'bg-[#0056D2] text-white shadow-lg shadow-[#0056D2]/10' : 'bg-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900'" 
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200">
                <i data-lucide="shield" class="w-5 h-5"></i>
                <span>Security & PIN</span>
            </button>
            
            <button @click="activeTab = 'upgrade'" 
                    :class="activeTab === 'upgrade' ? 'bg-[#0056D2] text-white shadow-lg shadow-[#0056D2]/10' : 'bg-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900'" 
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200">
                <i data-lucide="award" class="w-5 h-5"></i>
                <span>Account Upgrade</span>
            </button>
            
            <button @click="activeTab = 'danger'" 
                    :class="activeTab === 'danger' ? 'bg-rose-50 text-rose-600' : 'bg-transparent text-slate-600 hover:bg-rose-50 hover:text-rose-700'" 
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
                <span>Delete Account</span>
            </button>
        </div>

        <!-- Form Panels -->
        <div class="lg:col-span-3">
            <!-- Global Feedback Alerts -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-200">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 flex-shrink-0"></i>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any() && !$errors->updatePassword->any() && !$errors->updatePin->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-200">
                    <div class="flex items-center gap-3 mb-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-500 flex-shrink-0"></i>
                        <span class="text-sm font-bold">Please correct the following errors:</span>
                    </div>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs font-semibold">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- TAB 1: PERSONAL PROFILE -->
            <div x-show="activeTab === 'profile'" 
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
                
                <div>
                    <h2 class="text-lg font-bold text-slate-800 font-display">Personal Details</h2>
                    <p class="text-xs text-slate-400 mt-1">Manage your basic profile information, gender identity, and profile picture.</p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('patch')

                    <!-- Photo Upload Area -->
                    <div x-data="{ photoPreview: null }" class="flex flex-col sm:flex-row items-center gap-6 pb-6 border-b border-slate-100">
                        <div class="relative group">
                            @if ($user->profile_photo)
                                <img x-show="!photoPreview" 
                                     src="{{ asset($user->profile_photo) }}" 
                                     class="w-24 h-24 rounded-3xl object-cover border-4 border-slate-50 shadow-md group-hover:opacity-80 transition-all duration-200" 
                                     alt="Avatar">
                            @else
                                <div x-show="!photoPreview" class="w-24 h-24 rounded-3xl bg-[#0056D2]/10 text-[#0056D2] flex items-center justify-center font-bold text-3xl font-display border-4 border-slate-50 shadow-md group-hover:opacity-80 transition-all duration-200">
                                    {{ strtoupper(substr($user->first_name ?? $user->email, 0, 1)) }}
                                </div>
                            @endif

                            <img x-show="photoPreview" 
                                 :src="photoPreview" 
                                 class="w-24 h-24 rounded-3xl object-cover border-4 border-slate-50 shadow-md" 
                                 style="display: none;" 
                                 alt="Preview">

                            <!-- Overlay camera button -->
                            <label for="profile_photo" class="absolute inset-0 flex items-center justify-center bg-slate-900/40 rounded-3xl opacity-0 group-hover:opacity-100 cursor-pointer transition-all duration-200">
                                <i data-lucide="camera" class="w-6 h-6 text-white"></i>
                            </label>
                            
                            <input type="file" 
                                   id="profile_photo" 
                                   name="profile_photo" 
                                   class="hidden" 
                                   accept="image/*"
                                   @change="
                                       const reader = new FileReader();
                                       reader.onload = (e) => {
                                           photoPreview = e.target.result;
                                       };
                                       reader.readAsDataURL($event.target.files[0]);
                                   ">
                        </div>

                        <div class="text-center sm:text-left space-y-1.5">
                            <h4 class="text-sm font-bold text-slate-700">Avatar Photo</h4>
                            <p class="text-xs text-slate-400">Accepts PNG, JPG, or JPEG. Max file limit is 2MB.</p>
                            <button type="button" 
                                    @click="document.getElementById('profile_photo').click()" 
                                    class="px-4 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-600 font-semibold text-xs rounded-xl border border-slate-200 transition-all duration-150">
                                Choose New File
                            </button>
                        </div>
                    </div>

                    <!-- Input Grid (Read-only / Disabled) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">First Name</label>
                            <input type="text" 
                                   value="{{ old('first_name', $user->first_name) }}" 
                                   class="w-full px-4 py-3 bg-slate-100 border border-slate-200 focus:outline-none rounded-xl text-sm text-slate-500 font-semibold cursor-not-allowed" 
                                   disabled>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Middle Name <span class="text-slate-400 font-medium">(Optional)</span></label>
                            <input type="text" 
                                   value="{{ old('middle_name', $user->middle_name) }}" 
                                   class="w-full px-4 py-3 bg-slate-100 border border-slate-200 focus:outline-none rounded-xl text-sm text-slate-500 font-semibold cursor-not-allowed" 
                                   disabled>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Last Name</label>
                            <input type="text" 
                                   value="{{ old('last_name', $user->last_name) }}" 
                                   class="w-full px-4 py-3 bg-slate-100 border border-slate-200 focus:outline-none rounded-xl text-sm text-slate-500 font-semibold cursor-not-allowed" 
                                   disabled>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Email Address</label>
                            <input type="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   class="w-full px-4 py-3 bg-slate-100 border border-slate-200 focus:outline-none rounded-xl text-sm text-slate-500 font-semibold cursor-not-allowed" 
                                   disabled>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Phone Number</label>
                            <input type="text" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   class="w-full px-4 py-3 bg-slate-100 border border-slate-200 focus:outline-none rounded-xl text-sm text-slate-500 font-semibold cursor-not-allowed" 
                                   disabled>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Gender Identification</label>
                        <select name="gender" 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200">
                            <option value="" disabled selected>Select Gender</option>
                            <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" 
                                class="px-6 py-3.5 bg-gradient-to-r from-[#0056D2] to-[#0049b8] hover:from-[#003a8c] hover:to-[#0056D2] text-white font-semibold text-sm rounded-xl shadow-md transition-all duration-200 active:scale-[0.98]">
                            Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: SECURITY & PIN -->
            <div x-show="activeTab === 'security'" 
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="space-y-8" 
                 style="display: none;">
                
                <!-- Change password block -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 font-display">Account Password</h2>
                        <p class="text-xs text-slate-400 mt-1">Make sure you use a secure, complex password for your login security.</p>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                        @csrf
                        @method('put')

                        @if ($errors->updatePassword->any())
                            <div class="p-3 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl">
                                <ul class="list-disc pl-4 space-y-0.5">
                                    @foreach ($errors->updatePassword->all() as $error)
                                        <li class="text-xs font-semibold">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Current Password</label>
                            <input type="password" 
                                   name="current_password" 
                                   class="w-full px-4 py-3 bg-slate-50 border {{ $errors->updatePassword->has('current_password') ? 'border-rose-400' : 'border-slate-200' }} focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200" 
                                   required>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">New Password</label>
                                <input type="password" 
                                       name="password" 
                                       class="w-full px-4 py-3 bg-slate-50 border {{ $errors->updatePassword->has('password') ? 'border-rose-400' : 'border-slate-200' }} focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200" 
                                       required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Confirm New Password</label>
                                <input type="password" 
                                       name="password_confirmation" 
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200" 
                                       required>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" 
                                    class="px-6 py-3.5 bg-gradient-to-r from-[#0056D2] to-[#0049b8] hover:from-[#003a8c] hover:to-[#0056D2] text-white font-semibold text-sm rounded-xl shadow-md transition-all duration-200 active:scale-[0.98]">
                                Update Login Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Transaction PIN block -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800 font-display">Secure Transaction PIN</h2>
                            <p class="text-xs text-slate-400 mt-1">A 5-digit security PIN is required to authorize all wallet transactions and service purchases.</p>
                        </div>
                        <div class="shrink-0">
                            @if ($user->transaction_pin)
                                <span class="px-3 py-1 text-xs font-extrabold bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full uppercase tracking-wider flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                    Active
                                </span>
                                @if ($user->pin_set_at)
                                    <p class="text-xs text-slate-400 text-right mt-1">Last set {{ $user->pin_set_at->diffForHumans() }}</p>
                                @endif
                            @else
                                <span class="px-3 py-1 text-xs font-extrabold bg-amber-50 text-amber-600 border border-amber-100 rounded-full uppercase tracking-wider flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                                    Not Set
                                </span>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.pin.update') }}" class="space-y-6">
                        @csrf

                        @if ($errors->updatePin->any())
                            <div class="p-3 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl">
                                <ul class="list-disc pl-4 space-y-0.5">
                                    @foreach ($errors->updatePin->all() as $error)
                                        <li class="text-xs font-semibold">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Confirm Account Password</label>
                            <input type="password" 
                                   name="password" 
                                   placeholder="Enter password to authorise PIN update"
                                   class="w-full px-4 py-3 bg-slate-50 border {{ $errors->updatePin->has('password') ? 'border-rose-400' : 'border-slate-200' }} focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200" 
                                   required>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">New Transaction PIN <span class="text-rose-400">*</span></label>
                                <input type="password" 
                                       name="transaction_pin" 
                                       maxlength="5"
                                       minlength="5"
                                       pattern="[0-9]{5}" 
                                       inputmode="numeric"
                                       placeholder="5 digits"
                                       class="w-full px-4 py-3 bg-slate-50 border {{ $errors->updatePin->has('transaction_pin') ? 'border-rose-400' : 'border-slate-200' }} focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200 tracking-[0.6em] text-center" 
                                       required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Confirm Transaction PIN <span class="text-rose-400">*</span></label>
                                <input type="password" 
                                       name="transaction_pin_confirmation" 
                                       maxlength="5"
                                       minlength="5"
                                       pattern="[0-9]{5}" 
                                       inputmode="numeric"
                                       placeholder="Repeat 5 digits"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200 tracking-[0.6em] text-center" 
                                       required>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400 flex items-center gap-1.5">
                            <i data-lucide="info" class="w-3.5 h-3.5 text-slate-300 shrink-0"></i>
                            PIN must be exactly <strong>5 numeric digits</strong>. It is stored securely using bcrypt encryption and is never visible to anyone.
                        </p>

                        <div class="flex justify-end pt-2">
                            <button type="submit" 
                                    class="px-6 py-3.5 bg-gradient-to-r from-[#0056D2] to-[#0049b8] hover:from-[#003a8c] hover:to-[#0056D2] text-white font-semibold text-sm rounded-xl shadow-md transition-all duration-200 active:scale-[0.98]">
                                Save Transaction PIN
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 3: ACCOUNT UPGRADE -->
            <div x-show="activeTab === 'upgrade'" 
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6" 
                 style="display: none;">
                
                <div>
                    <h2 class="text-lg font-bold text-slate-800 font-display">Upgrade Account Status</h2>
                    <p class="text-xs text-slate-400 mt-1">Upgrade your tier status and access bulk discounts by updating to an Agent, Partner, or Business tier.</p>
                </div>

                <!-- Request Status Info Banners -->
                @if ($user->upgrade_status === 'pending')
                    <div class="p-4 bg-amber-50 border border-amber-100 text-amber-700 rounded-2xl flex items-start gap-3">
                        <i data-lucide="clock" class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5 animate-pulse"></i>
                        <div>
                            <span class="block text-sm font-bold">Upgrade Request Pending</span>
                            <span class="block text-xs mt-0.5 font-medium leading-relaxed">
                                Your application to upgrade to <strong class="uppercase">{{ $user->pending_role }}</strong> was submitted on {{ $user->upgrade_requested_at->format('M d, Y h:i A') }} and is currently under review by our administrators.
                            </span>
                        </div>
                    </div>
                @elseif ($user->upgrade_status === 'rejected')
                    <div class="p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl flex items-start gap-3">
                        <i data-lucide="x-circle" class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <span class="block text-sm font-bold">Upgrade Application Declined</span>
                            <span class="block text-xs mt-0.5 font-medium leading-relaxed">
                                Your previous upgrade application was declined. You may review your business information below and re-submit a new application.
                            </span>
                        </div>
                    </div>
                @elseif ($user->upgrade_status === 'approved')
                    <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-start gap-3">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <span class="block text-sm font-bold">Upgrade Request Approved</span>
                            <span class="block text-xs mt-0.5 font-medium leading-relaxed">
                                Congratulations! Your upgrade to <strong class="uppercase">{{ $user->role }}</strong> has been approved. You are now enjoying premium rates and API permissions.
                            </span>
                        </div>
                    </div>
                @endif

                @if ($user->upgrade_status === 'pending' || $user->upgrade_status === 'approved' || in_array($user->role, ['agent', 'partner', 'business']))
                    <!-- Read-only Summary Cards -->
                    <div class="bg-slate-50 border border-slate-200/60 rounded-3xl p-6 sm:p-8 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Target Account Tier</label>
                                <div class="px-4 py-3 bg-slate-100/50 border border-slate-200 rounded-xl text-sm text-slate-700 font-semibold capitalize">
                                    {{ $user->pending_role ?? $user->role }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Confirm Gender</label>
                                <div class="px-4 py-3 bg-slate-100/50 border border-slate-200 rounded-xl text-sm text-slate-700 font-semibold capitalize">
                                    {{ $user->gender }}
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200/60 pt-6 space-y-6">
                            <h4 class="text-sm font-bold text-slate-700">Business Registration & Credentials</h4>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Registered Business Name</label>
                                <div class="px-4 py-3 bg-slate-100/50 border border-slate-200 rounded-xl text-sm text-slate-700 font-semibold">
                                    {{ $user->business_name }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Business Category Type</label>
                                    <div class="px-4 py-3 bg-slate-100/50 border border-slate-200 rounded-xl text-sm text-slate-700 font-semibold capitalize">
                                        {{ str_replace('_', ' ', $user->business_type) }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">CAC Registration Number</label>
                                    <div class="px-4 py-3 bg-slate-100/50 border border-slate-200 rounded-xl text-sm text-slate-700 font-semibold">
                                        {{ $user->cac_number }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('profile.upgrade.request') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Target Account Tier</label>
                                <select name="role" 
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200">
                                    <option value="" disabled selected>Select Tier </option>
                                    <option value="agent" {{ old('role', $user->pending_role ?? $user->role) == 'agent' ? 'selected' : '' }}>Agent (Sub-distributor/Reseller benefits)</option>
                                    <option value="partner" {{ old('role', $user->pending_role ?? $user->role) == 'partner' ? 'selected' : '' }}>Partner (Integrator APIs & custom values)</option>
                                    <option value="business" {{ old('role', $user->pending_role ?? $user->role) == 'business' ? 'selected' : '' }}>Business (Wholesale pricing tiers)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Confirm Gender</label>
                                <select name="gender" 
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200">
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-6 space-y-6">
                            <h4 class="text-sm font-bold text-slate-700">Business Registration & Credentials</h4>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Registered Business Name</label>
                                <input type="text" 
                                       name="business_name" 
                                       value="{{ old('business_name', $user->business_name) }}" 
                                       placeholder="e.g. Smart Telecoms Ltd"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200" 
                                       required>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Business Category Type</label>
                                    <select name="business_type" 
                                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200">
                                        <option value="" disabled selected>Select Category</option>
                                        <option value="sole_proprietor" {{ old('business_type', $user->business_type) == 'sole_proprietor' ? 'selected' : '' }}>Sole Proprietorship</option>
                                        <option value="llc" {{ old('business_type', $user->business_type) == 'llc' ? 'selected' : '' }}>Limited Liability Company (LLC)</option>
                                        <option value="partnership" {{ old('business_type', $user->business_type) == 'partnership' ? 'selected' : '' }}>Partnership</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">CAC Registration Number</label>
                                    <input type="text" 
                                           name="cac_number" 
                                           value="{{ old('cac_number', $user->cac_number) }}" 
                                           placeholder="e.g. RC-1234567"
                                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" 
                                    class="px-6 py-3.5 bg-gradient-to-r from-[#0056D2] to-[#0049b8] hover:from-[#003a8c] hover:to-[#0056D2] text-white font-semibold text-sm rounded-xl shadow-md transition-all duration-200 active:scale-[0.98]">
                                Submit Upgrade Request
                            </button>
                        </div>
                    </form>
                @endif
            </div>
            </div>

            <!-- TAB 4: DANGER ZONE -->
            <div x-show="activeTab === 'danger'" 
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white p-6 sm:p-8 rounded-3xl border border-rose-100 shadow-sm space-y-6" 
                 style="display: none;">
                
                <div>
                    <h2 class="text-lg font-bold text-rose-700 font-display">Deactivate Account</h2>
                    <p class="text-xs text-slate-400 mt-1">Once your account is deactivated, you will be logged out and your access will be suspended.</p>
                </div>

                <div class="p-4 bg-rose-50 border border-rose-100 rounded-2xl">
                    <p class="text-xs text-rose-700 font-medium leading-relaxed">
                        Warning: Deactivating your account will result in immediate loss of access to your dashboard, transaction logs, and API credentials. Your profile data and history will be safely preserved but locked.
                    </p>
                </div>

                <div class="flex justify-start">
                    <button type="button" 
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                            class="px-6 py-3.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm rounded-xl shadow-md transition-all duration-200 active:scale-[0.98]">
                        Deactivate My SmartSIM Account
                    </button>
                </div>

                <!-- Breeze Modal Integration -->
                <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                    <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8 space-y-6">
                        @csrf
                        @method('delete')

                        <div>
                            <h2 class="text-lg font-bold text-slate-800 font-display">
                                Are you sure you want to deactivate your account?
                            </h2>
                            <p class="text-xs text-slate-400 mt-1">
                                Please type your password to confirm that you want to deactivate this account.
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Account Password</label>
                            <input type="password" 
                                   name="password" 
                                   placeholder="Type account password"
                                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200" 
                                   required>
                            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" 
                                    x-on:click="$dispatch('close')"
                                    class="px-5 py-3 bg-slate-50 hover:bg-slate-100 text-slate-600 font-semibold text-sm rounded-xl border border-slate-200 transition-all">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-5 py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm rounded-xl transition-all">
                                Deactivate Account
                            </button>
                        </div>
                    </form>
                </x-modal>
            </div>

        </div>
    </div>
</x-app-layout>
