<x-app-layout>
    <div class="space-y-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight font-display">Manage Access Control</h1>
                <p class="text-xs text-slate-400 mt-1">Configure user access levels, system permissions, and promote users between roles.</p>
            </div>
        </div>

        <!-- Success Alert -->
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 flex-shrink-0"></i>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Card Section -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <!-- Search -->
            <div class="p-6 border-b border-slate-50 bg-slate-50/50">
                <form method="GET" action="{{ route('admin.manage.access') }}" class="w-full sm:max-w-md relative">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search user by name or email..." 
                               class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200">
                        <div class="absolute left-4 top-3.5 text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">User Details</th>
                            <th class="py-4 px-6">Current Access level</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6 text-right">Modify Access Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm font-medium text-slate-700">
                        @forelse ($users as $u)
                            <tr class="hover:bg-slate-50/30 transition-all duration-150">
                                <td class="py-4 px-6">
                                    <span class="block text-slate-800 font-bold font-display">{{ $u->first_name }} {{ $u->last_name }}</span>
                                    <span class="block text-xs font-semibold text-slate-400">{{ $u->email }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-full bg-[#0056D2]/10 text-[#0056D2] border border-[#0056D2]/20 uppercase tracking-wider">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    @if ($u->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[9px] font-extrabold bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full uppercase tracking-wider">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[9px] font-extrabold bg-slate-100 text-slate-500 border border-slate-200 rounded-full uppercase tracking-wider">
                                            {{ $u->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <form method="POST" action="{{ route('admin.manage.access.update', $u) }}" class="inline-flex items-center gap-3">
                                        @csrf
                                        @method('PUT')
                                        
                                        <select name="role" 
                                                class="px-3 py-1.5 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:outline-none rounded-xl text-xs text-slate-850 font-semibold transition-all duration-150">
                                            <option value="personal" {{ $u->role === 'personal' ? 'selected' : '' }}>Personal</option>
                                            <option value="agent" {{ $u->role === 'agent' ? 'selected' : '' }}>Agent</option>
                                            <option value="partner" {{ $u->role === 'partner' ? 'selected' : '' }}>Partner</option>
                                            <option value="business" {{ $u->role === 'business' ? 'selected' : '' }}>Business</option>
                                            <option value="staff" {{ $u->role === 'staff' ? 'selected' : '' }}>Staff</option>
                                            <option value="checker" {{ $u->role === 'checker' ? 'selected' : '' }}>Checker</option>
                                            <option value="super_admin" {{ $u->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                        </select>

                                        <button type="submit" 
                                                class="px-4 py-1.5 bg-[#0056D2] hover:bg-[#003a8c] text-white font-semibold text-xs rounded-xl shadow-sm transition-all duration-150 active:scale-[0.98]">
                                            Save
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-slate-400 text-sm">
                                    No registered users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{ $users->withQueryString()->links('vendor.pagination.custom') }}
        </div>
    </div>
</x-app-layout>
