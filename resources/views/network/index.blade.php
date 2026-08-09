<x-app-layout>
    <title>SmartSIM - My Network</title>

    <div x-data="{ openClaimModal: false, openClaimResultModal: {{ session('claim_result') ? 'true' : 'false' }} }" class="max-w-5xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold font-display text-slate-800 flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <i data-lucide="network" class="w-5 h-5"></i>
                    </div>
                    My Network
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    @if ($nextRole)
                        The {{ str_replace('_', ' ', $nextRole) }}s you've onboarded, and everyone below them in your downline.
                    @else
                        The people in your downline.
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if ($nextRole)
                    <button type="button" @click="openClaimModal = true"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white border border-slate-300 text-slate-700 font-semibold text-xs shadow-sm transition-all hover:bg-slate-50 hover:border-slate-400 active:scale-[0.98] whitespace-nowrap">
                        <i data-lucide="search-check" class="w-3.5 h-3.5 text-slate-500"></i>
                        Claim Member
                    </button>
                @endif

                @if ($nextRole)
                    <a href="{{ route('onboarding.invite') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white font-semibold text-xs shadow-sm transition-all hover:bg-primary/90 active:scale-[0.98] whitespace-nowrap">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                        Invite a {{ str_replace('_', ' ', $nextRole) }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4 text-emerald-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border border-red-100 rounded-lg p-4 text-red-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card padding="p-5" class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Total Downline</p>
                    <p class="text-lg font-bold font-display text-slate-800">{{ number_format($totalReferrals) }}</p>
                </div>
            </x-card>
            <x-card padding="p-5" class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Active Members</p>
                    <p class="text-lg font-bold font-display text-slate-800">{{ number_format($activeReferrals) }}</p>
                </div>
            </x-card>
            <x-card padding="p-5" class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Pending Invites</p>
                    <p class="text-lg font-bold font-display text-slate-800">{{ number_format($pendingInvites) }}</p>
                </div>
            </x-card>
        </div>

        @if ($roleBreakdown->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach ($roleBreakdown as $role => $count)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                        {{ $count }} {{ str_replace('_', ' ', $role) }}{{ $count === 1 ? '' : 's' }}
                    </span>
                @endforeach
            </div>
        @endif

        <!-- Downline Table -->
        <x-card padding="p-6">
            <h3 class="text-sm font-semibold text-slate-800 font-display pb-3 border-b border-slate-100 mb-4">
                Your Downline
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase">
                            <th class="py-2.5">Member</th>
                            <th class="py-2.5">Role</th>
                            <th class="py-2.5">Status</th>
                            <th class="py-2.5">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($referrals as $ref)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                                <td class="py-3">
                                    <span class="font-bold text-slate-800 block">{{ $ref->first_name ? $ref->first_name . ' ' . $ref->last_name : $ref->email }}</span>
                                    <span class="text-xs text-slate-400">{{ $ref->email }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase bg-primary/10 text-primary">
                                        {{ str_replace('_', ' ', $ref->role) }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase
                                        {{ $ref->status === 'active' ? 'bg-emerald-50 text-emerald-600' : ($ref->status === 'invited' ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $ref->status }}
                                    </span>
                                </td>
                                <td class="py-3 text-slate-400 font-medium">{{ $ref->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-slate-400">
                                    <div class="bg-slate-50 rounded-2xl w-14 h-14 flex items-center justify-center mb-3 mx-auto border border-slate-100">
                                        <i data-lucide="user-plus" class="w-6 h-6 text-slate-400"></i>
                                    </div>
                                    <h6 class="font-bold text-slate-800 text-sm">No downline members yet</h6>
                                    <p class="text-xs text-slate-400 mt-1 max-w-[280px] mx-auto">
                                        @if ($nextRole)
                                            Invite a {{ str_replace('_', ' ', $nextRole) }} above, or share your sign-up link below.
                                        @else
                                            Share your sign-up link below — anyone who joins through it will appear here.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $referrals->links('vendor.pagination.custom') }}
        </x-card>

        <!-- Public Sign-up Link -->
        <x-card>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <i data-lucide="link" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 font-display">Public Sign-up Link</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Anyone who registers through this link joins your network as a standard User account.</p>
                </div>
            </div>

            <div x-data="{ copied: false }" class="flex flex-col sm:flex-row gap-2">
                <div class="flex-1 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 truncate">
                    {{ $referralLink }}
                </div>
                <button type="button"
                        @click="navigator.clipboard.writeText('{{ $referralLink }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-slate-800 text-white font-semibold text-xs shadow-sm transition-all hover:bg-slate-700 active:scale-[0.98] shrink-0">
                    <i data-lucide="copy" class="w-3.5 h-3.5" x-show="!copied"></i>
                    <i data-lucide="check" class="w-3.5 h-3.5" x-show="copied" x-cloak></i>
                    <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                </button>
            </div>

            <p class="text-xs text-slate-400 mt-3">
                Referral code: <span class="font-mono font-bold text-slate-600">{{ $user->referral_code }}</span>
            </p>
        </x-card>

        @if ($nextRole)
            <!-- Claim Downline Member Modal -->
            <div x-show="openClaimModal"
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="display: none;">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openClaimModal = false"></div>
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="relative w-full max-w-md bg-white rounded-xl border border-slate-200 shadow-2xl p-6 overflow-hidden transform transition-all">
                        <button type="button" @click="openClaimModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <i data-lucide="search-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-800 font-display">Claim a Downline Member</h3>
                                <p class="text-xs text-slate-400">Look up a self-onboarded User by phone number. If it's unclaimed, you can add them to your network.</p>
                            </div>
                        </div>
                        <form action="{{ route('network.claim.check') }}" method="GET" class="space-y-4"
                              x-data="{ phone: '', submitting: false }"
                              @submit="if (phone.length !== 11) { $event.preventDefault(); return; } submitting = true">
                            <div class="space-y-1.5">
                                <x-input-label for="claim_phone" value="Phone Number" />
                                <x-text-input type="tel" id="claim_phone" name="phone" required
                                       x-model="phone"
                                       @input="phone = phone.replace(/[^0-9]/g, '').slice(0, 11)"
                                       inputmode="numeric" maxlength="11" pattern="[0-9]{11}"
                                       autocomplete="off"
                                       class="rounded-xl text-center font-semibold"
                                       x-bind:class="phone.length > 0 && phone.length !== 11 ? 'border-rose-300 focus:border-rose-400 focus:ring-rose-100' : ''"
                                       placeholder="e.g. 08031234567" />
                                <p x-show="phone.length > 0 && phone.length !== 11" x-cloak class="text-xs font-medium text-rose-500">
                                    Enter a valid 11-digit phone number.
                                </p>
                            </div>
                            <x-primary-button type="submit" class="w-full !text-xs !bg-slate-800 hover:!bg-slate-700"
                                               x-bind:disabled="phone.length !== 11 || submitting">
                                <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="submitting" x-cloak></i>
                                <i data-lucide="search" class="w-3.5 h-3.5" x-show="!submitting"></i>
                                <span x-text="submitting ? 'Checking…' : 'Check Number'"></span>
                            </x-primary-button>
                        </form>
                    </div>
                </div>
            </div>

            @if (session('claim_result'))
                @php $claimResult = session('claim_result'); @endphp
                <!-- Claim Result Modal -->
                <div x-show="openClaimResultModal"
                     class="fixed inset-0 z-50 overflow-y-auto"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     style="display: none;">
                    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openClaimResultModal = false"></div>
                    <div class="flex min-h-screen items-center justify-center p-4">
                        <div class="relative w-full max-w-md bg-white rounded-xl border border-slate-200 shadow-2xl p-6 overflow-hidden transform transition-all space-y-4">
                            <button type="button" @click="openClaimResultModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                                <div class="w-9 h-9 rounded-xl {{ $claimResult['success'] ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center">
                                    <i data-lucide="{{ $claimResult['success'] ? 'user-check' : 'alert-circle' }}" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-800 font-display">Claim Lookup Result</h3>
                                </div>
                            </div>

                            @if (!$claimResult['success'])
                                <p class="text-sm text-rose-600 font-semibold">{{ $claimResult['message'] }}</p>
                            @else
                                <div class="space-y-3">
                                    <p class="text-sm text-emerald-600 font-semibold">This account is unclaimed and eligible to join your network.</p>
                                    <div class="bg-primary/5 rounded-lg p-4 border border-primary/10 space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-slate-500 font-semibold text-xs">Name:</span>
                                            <span class="font-bold text-slate-800">{{ $claimResult['name'] }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-slate-500 font-semibold text-xs">Email:</span>
                                            <span class="font-bold text-slate-800 text-xs break-all">{{ $claimResult['email'] }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-slate-500 font-semibold text-xs">Phone:</span>
                                            <span class="font-bold text-slate-800">{{ $claimResult['phone'] }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm pt-2 border-t border-primary/10">
                                            <span class="text-slate-500 font-semibold text-xs">Will become:</span>
                                            <span class="font-bold text-primary capitalize">{{ $claimResult['role_label'] }}</span>
                                        </div>
                                    </div>
                                    <form action="{{ route('network.claim') }}" method="POST"
                                          x-data="{ submitting: false }" @submit="submitting = true">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $claimResult['user_id'] }}">
                                        <x-primary-button type="submit" class="w-full !text-xs" x-bind:disabled="submitting">
                                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="submitting" x-cloak></i>
                                            <i data-lucide="user-check" class="w-3.5 h-3.5" x-show="!submitting"></i>
                                            <span x-text="submitting ? 'Claiming…' : 'Claim Into My Network'"></span>
                                        </x-primary-button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
