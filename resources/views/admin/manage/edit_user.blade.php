<x-app-layout>
    <div class="space-y-8 max-w-3xl mx-auto">
        <!-- Back Link & Title -->
        <div class="space-y-4">
            <a href="{{ route('admin.manage.users') }}" 
               class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors duration-150">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Users List
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight font-display">Edit User details</h1>
                <p class="text-xs text-slate-400 mt-1">Modify account settings, tier permissions, and state status for {{ $user->first_name }}.</p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.manage.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name Details -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">First Name</label>
                        <input type="text" 
                               name="first_name" 
                               value="{{ old('first_name', $user->first_name) }}" 
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200" 
                               required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Middle Name</label>
                        <input type="text" 
                               name="middle_name" 
                               value="{{ old('middle_name', $user->middle_name) }}" 
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Last Name</label>
                        <input type="text" 
                               name="last_name" 
                               value="{{ old('last_name', $user->last_name) }}" 
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200" 
                               required>
                    </div>
                </div>

                <!-- Contact details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Email Address</label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200" 
                               required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Phone Number</label>
                        <input type="text" 
                               name="phone" 
                               value="{{ old('phone', $user->phone) }}" 
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200" 
                               required>
                    </div>
                </div>

                <!-- Settings & Access -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">User Status</label>
                        <select name="status" 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200">
                            <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive / Deactivated</option>
                            <option value="banned" {{ old('status', $user->status) == 'banned' ? 'selected' : '' }}>Banned</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">System Role</label>
                        <select name="role" 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-xl text-sm text-slate-800 font-semibold transition-all duration-200">
                            <option value="personal" {{ old('role', $user->role) == 'personal' ? 'selected' : '' }}>Personal</option>
                            <option value="agent" {{ old('role', $user->role) == 'agent' ? 'selected' : '' }}>Agent</option>
                            <option value="partner" {{ old('role', $user->role) == 'partner' ? 'selected' : '' }}>Partner</option>
                            <option value="business" {{ old('role', $user->role) == 'business' ? 'selected' : '' }}>Business</option>
                            <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="checker" {{ old('role', $user->role) == 'checker' ? 'selected' : '' }}>Checker</option>
                            <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4 border-t border-slate-50">
                    <button type="submit" 
                            class="px-6 py-3.5 bg-gradient-to-r from-[#0056D2] to-[#0049b8] hover:from-[#003a8c] hover:to-[#0056D2] text-white font-semibold text-sm rounded-xl shadow-md transition-all duration-200 active:scale-[0.98]">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
