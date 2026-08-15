<x-app-layout>
    <title>SmartSIM - My Network</title>

    @php $canRestrict = \App\Support\SimAccess::canBrowseCatalog(auth()->user()); @endphp
    <div x-data="{ openClaimModal: false, openClaimResultModal: {{ session('claim_result') ? 'true' : 'false' }}, currentTab: 'invited', openMemberModal: false, selectedMember: null }" class="max-w-5xl mx-auto space-y-6">
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

        <!-- Invited / Team Tabs -->
        <x-card padding="p-6">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100 mb-4">
                <button type="button" @click="currentTab = 'invited'"
                        :class="currentTab === 'invited' ? 'bg-[#0056D2] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-colors">
                    Invited ({{ $invited->total() }})
                </button>
                <button type="button" @click="currentTab = 'team'"
                        :class="currentTab === 'team' ? 'bg-[#0056D2] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition-colors">
                    Team ({{ $team->total() }})
                </button>
            </div>

            <!-- Invited Panel -->
            <div x-show="currentTab === 'invited'">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase">
                                <th class="py-2.5">Member</th>
                                <th class="py-2.5">Role</th>
                                <th class="py-2.5">Invited</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invited as $ref)
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
                                    <td class="py-3 text-slate-400 font-medium">{{ $ref->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-12 text-center text-slate-400">
                                        <div class="bg-slate-50 rounded-2xl w-14 h-14 flex items-center justify-center mb-3 mx-auto border border-slate-100">
                                            <i data-lucide="mail" class="w-6 h-6 text-slate-400"></i>
                                        </div>
                                        <h6 class="font-bold text-slate-800 text-sm">No pending invites</h6>
                                        <p class="text-xs text-slate-400 mt-1 max-w-[280px] mx-auto">
                                            @if ($nextRole)
                                                Invite a {{ str_replace('_', ' ', $nextRole) }} above — they'll show up here until they accept.
                                            @else
                                                Accounts you invite will show up here until they accept.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $invited->links('vendor.pagination.custom') }}
            </div>

            <!-- Team Panel -->
            <div x-show="currentTab === 'team'" x-cloak>
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
                            @forelse ($team as $ref)
                                @php
                                    $isLocked = $ref->wallet->is_locked ?? false;
                                    $isSuspended = $ref->isSuspended();
                                @endphp
                                <tr class="border-b border-slate-50 hover:bg-slate-50/50 {{ $canRestrict ? 'cursor-pointer' : '' }}"
                                    @if ($canRestrict)
                                        @click="selectedMember = {
                                            id: {{ $ref->id }},
                                            name: '{{ addslashes(($ref->first_name ? $ref->first_name . ' ' . $ref->last_name : $ref->email)) }}',
                                            role: '{{ addslashes(str_replace('_', ' ', $ref->role)) }}',
                                            status: '{{ $ref->status }}',
                                            phone: '{{ addslashes($ref->phone ?? '—') }}',
                                            email: '{{ addslashes($ref->email) }}',
                                            joined: '{{ $ref->created_at->format('M d, Y') }}',
                                            isLocked: {{ $isLocked ? 'true' : 'false' }},
                                            isSuspended: {{ $isSuspended ? 'true' : 'false' }}
                                        }; openMemberModal = true"
                                    @endif
                                    >
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
                                        <div class="flex flex-wrap items-center gap-1">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase
                                                {{ $ref->status === 'active' ? 'bg-emerald-50 text-emerald-600' : ($ref->status === 'suspended' ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-600') }}">
                                                {{ $ref->status }}
                                            </span>
                                            @if ($isLocked)
                                                <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase bg-amber-50 text-amber-600">PND</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 text-slate-400 font-medium">{{ $ref->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-400">
                                        <div class="bg-slate-50 rounded-2xl w-14 h-14 flex items-center justify-center mb-3 mx-auto border border-slate-100">
                                            <i data-lucide="users" class="w-6 h-6 text-slate-400"></i>
                                        </div>
                                        <h6 class="font-bold text-slate-800 text-sm">No team members yet</h6>
                                        <p class="text-xs text-slate-400 mt-1 max-w-[280px] mx-auto">
                                            Once an invited member accepts, they'll appear here.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $team->links('vendor.pagination.custom') }}
            </div>
        </x-card>

        @if ($canRestrict)
            <!-- Team Member Detail Modal -->
            <div x-show="openMemberModal" x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="display: none;">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="openMemberModal = false"></div>
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="relative w-full max-w-md bg-white rounded-xl border border-slate-200 shadow-2xl p-6 overflow-hidden transform transition-all space-y-4" x-show="selectedMember">
                        <button type="button" @click="openMemberModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-800 font-display" x-text="selectedMember ? selectedMember.name : ''"></h3>
                                <p class="text-xs text-slate-400 capitalize" x-text="selectedMember ? selectedMember.role : ''"></p>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-100 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-semibold text-xs">Status:</span>
                                <span class="font-bold text-slate-800 capitalize" x-text="selectedMember ? selectedMember.status : ''"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-semibold text-xs">Phone:</span>
                                <span class="font-bold text-slate-800" x-text="selectedMember ? selectedMember.phone : ''"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-semibold text-xs">Email:</span>
                                <span class="font-bold text-slate-800 text-xs break-all" x-text="selectedMember ? selectedMember.email : ''"></span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-200">
                                <span class="text-slate-500 font-semibold text-xs">Joined:</span>
                                <span class="font-bold text-slate-800" x-text="selectedMember ? selectedMember.joined : ''"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <button type="button"
                                    x-show="selectedMember && !selectedMember.isLocked"
                                    @click="placeRestriction('pnd', selectedMember.id)"
                                    class="px-3 py-2 rounded-lg bg-amber-50 text-amber-700 border border-amber-100 text-xs font-bold hover:bg-amber-100 transition-colors">
                                Place PND
                            </button>
                            <button type="button"
                                    x-show="selectedMember && selectedMember.isLocked"
                                    @click="liftRestriction('pnd/lift', selectedMember.id)"
                                    class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold hover:bg-slate-200 transition-colors">
                                Lift PND
                            </button>
                            <button type="button"
                                    x-show="selectedMember && !selectedMember.isSuspended"
                                    @click="placeRestriction('suspend', selectedMember.id)"
                                    class="px-3 py-2 rounded-lg bg-rose-50 text-rose-700 border border-rose-100 text-xs font-bold hover:bg-rose-100 transition-colors">
                                Suspend
                            </button>
                            <button type="button"
                                    x-show="selectedMember && selectedMember.isSuspended"
                                    @click="liftRestriction('reinstate', selectedMember.id)"
                                    class="px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 text-xs font-bold hover:bg-emerald-100 transition-colors">
                                Reinstate
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

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

    @if ($canRestrict)
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function submitDownlineAction(path, userId, extra) {
                const f = document.createElement('form');
                f.action = '/network/downline/' + userId + '/' + path;
                f.method = 'POST';
                let inputs = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
                if (extra) {
                    inputs += '<input type="hidden" name="reason" value="' + extra.replace(/"/g, '&quot;') + '">';
                }
                f.innerHTML = inputs;
                document.body.appendChild(f);
                f.submit();
            }

            function placeRestriction(path, userId) {
                const isSuspend = path === 'suspend';
                Swal.fire({
                    title: isSuspend ? 'Suspend Account' : 'Place Post No Debit',
                    text: isSuspend
                        ? 'This blocks the member from logging in until reinstated.'
                        : 'This blocks the member from cashing out until lifted — other spending stays available.',
                    input: 'text',
                    inputLabel: 'Reason',
                    inputPlaceholder: 'Enter a reason...',
                    showCancelButton: true,
                    confirmButtonColor: isSuspend ? '#e11d48' : '#d97706',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: isSuspend ? 'Suspend' : 'Place PND',
                    preConfirm: (value) => {
                        if (!value || !value.trim()) {
                            Swal.showValidationMessage('A reason is required');
                        }
                        return value;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitDownlineAction(path, userId, result.value);
                    }
                });
            }

            function liftRestriction(path, userId) {
                Swal.fire({
                    title: 'Are you sure?',
                    showCancelButton: true,
                    confirmButtonColor: '#0056D2',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, confirm'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitDownlineAction(path, userId, null);
                    }
                });
            }
        </script>
    @endif
</x-app-layout>
