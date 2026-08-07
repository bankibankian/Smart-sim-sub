<x-app-layout>
    <div x-data="{ 
        addModalOpen: false, 
        editModalOpen: false, 
        editService: { id: '', name: '', description: '', image: '', isActive: true }
    }" class="space-y-8 max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="relative overflow-hidden p-6 sm:p-8 bg-white border border-slate-100/80 rounded-3xl shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-grid-pattern">
            <div class="absolute -right-16 -top-16 w-36 h-36 rounded-full bg-slate-50 blur-2xl opacity-50"></div>
            <div class="relative z-10">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight font-display bg-gradient-to-r from-slate-900 via-slate-800 to-[#0056D2] bg-clip-text text-transparent">Services Management</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 font-medium">Configure and manage system services, dynamic variant fields, and custom pricing rules.</p>
            </div>
            <div class="relative z-10 flex-shrink-0">
                <button @click="addModalOpen = true" class="inline-flex items-center gap-2 px-5 py-3 text-xs font-bold bg-gradient-to-r from-[#0056D2] to-[#0049b8] hover:from-[#37446b] hover:to-[#0056D2] text-white rounded-2xl shadow-md hover:shadow-lg shadow-[#0056D2]/10 hover:shadow-[#0056D2]/20 hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-200">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Add Service
                </button>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50/70 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0 shadow-inner">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50/70 border border-rose-100 text-rose-700 rounded-2xl flex items-center gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 flex-shrink-0 shadow-inner">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                </div>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Total Services -->
            <div class="relative overflow-hidden p-6 rounded-3xl text-white bg-gradient-to-br from-violet-600 via-indigo-700 to-indigo-800 glow-purple border border-white/15 transition-all duration-350 hover:scale-[1.03] hover:-translate-y-1 hover:shadow-xl hover:shadow-violet-600/20 group">
                <div class="absolute -right-8 -bottom-8 w-28 h-28 rounded-full bg-white/10 blur-xl group-hover:scale-125 transition-transform duration-500"></div>
                <div class="absolute right-4 top-4 w-14 h-14 rounded-full bg-white/5 border border-white/10 backdrop-blur-sm"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-xs font-semibold text-white/75 uppercase tracking-wider">Total Services</p>
                        <h3 class="text-3xl font-extrabold text-white mt-2.5 tracking-tight font-display">{{ $totalServicesCount }}</h3>
                        <p class="text-[11px] text-white/60 mt-2 font-medium flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-300"></span>
                            Configured in System
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-white/15 border border-white/10 flex items-center justify-center text-white backdrop-blur-md shadow-inner transition-transform duration-300 group-hover:rotate-6">
                        <i data-lucide="server" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>

            <!-- Active Services -->
            <div class="relative overflow-hidden p-6 rounded-3xl text-white bg-gradient-to-br from-emerald-500 via-teal-550 to-teal-700 glow-emerald border border-white/15 transition-all duration-350 hover:scale-[1.03] hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-600/20 group">
                <div class="absolute -right-8 -bottom-8 w-28 h-28 rounded-full bg-white/10 blur-xl group-hover:scale-125 transition-transform duration-500"></div>
                <div class="absolute right-4 top-4 w-14 h-14 rounded-full bg-white/5 border border-white/10 backdrop-blur-sm"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-xs font-semibold text-white/75 uppercase tracking-wider">Active Services</p>
                        <h3 class="text-3xl font-extrabold text-white mt-2.5 tracking-tight font-display">{{ $activeServicesCount }}</h3>
                        <p class="text-[11px] text-white/60 mt-2 font-medium flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                            Available to Users
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-white/15 border border-white/10 flex items-center justify-center text-white backdrop-blur-md shadow-inner transition-transform duration-300 group-hover:rotate-6">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>

            <!-- Inactive Services -->
            <div class="relative overflow-hidden p-6 rounded-3xl text-white bg-gradient-to-br from-rose-500 via-rose-600 to-red-700 glow-rose border border-white/15 transition-all duration-350 hover:scale-[1.03] hover:-translate-y-1 hover:shadow-xl hover:shadow-rose-600/20 group">
                <div class="absolute -right-8 -bottom-8 w-28 h-28 rounded-full bg-white/10 blur-xl group-hover:scale-125 transition-transform duration-500"></div>
                <div class="absolute right-4 top-4 w-14 h-14 rounded-full bg-white/5 border border-white/10 backdrop-blur-sm"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-xs font-semibold text-white/75 uppercase tracking-wider">Inactive Services</p>
                        <h3 class="text-3xl font-extrabold text-white mt-2.5 tracking-tight font-display">{{ $inactiveServicesCount }}</h3>
                        <p class="text-[11px] text-white/60 mt-2 font-medium flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-300"></span>
                            Temporarily Disabled
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-white/15 border border-white/10 flex items-center justify-center text-white backdrop-blur-md shadow-inner transition-transform duration-300 group-hover:rotate-6">
                        <i data-lucide="slash" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-md shadow-slate-100/40 overflow-hidden">
            <!-- Search & Filters -->
            <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-slate-50/50 via-white to-slate-50/20">
                <form method="GET" action="{{ route('admin.services.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-4">
                    <!-- Search Input -->
                    <div class="relative flex-grow">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search by service name or description..." 
                               class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-2xl text-sm text-slate-800 font-semibold transition-all duration-200 shadow-sm placeholder:text-slate-400">
                        <div class="absolute left-4 top-4 text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if(request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif
                    </div>

                    <!-- Status Filter -->
                    <div class="w-full lg:w-48">
                        <div class="relative">
                            <select name="status" onchange="this.form.submit()" class="w-full pl-3 pr-8 py-3 bg-white border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-2xl text-xs font-bold text-slate-600 transition-all duration-200 shadow-sm appearance-none cursor-pointer">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                            </select>
                            <div class="absolute right-3.5 top-4 pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Sort Filter -->
                    <div class="w-full lg:w-48">
                        <div class="relative">
                            <select name="sort" onchange="this.form.submit()" class="w-full pl-3 pr-8 py-3 bg-white border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-2xl text-xs font-bold text-slate-600 transition-all duration-200 shadow-sm appearance-none cursor-pointer">
                                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            </select>
                            <div class="absolute right-3.5 top-4 pointer-events-none text-slate-400">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Reset Button -->
                    @if(request('search') || request('status') || request('sort'))
                        <div class="flex items-center flex-shrink-0">
                            <a href="{{ route('admin.services.index') }}" class="w-full lg:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-3 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200/50 rounded-2xl transition-all duration-150 hover:shadow-sm">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                Reset
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-extrabold text-slate-400 uppercase tracking-wider bg-slate-50/30">
                            <th class="py-4 px-6 text-center w-16">S/N</th>
                            <th class="py-4 px-6">Service Details</th>
                            <th class="py-4 px-6">Variants</th>
                            <th class="py-4 px-6">Custom Prices</th>
                            <th class="py-4 px-6">Created Date</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6 text-right pr-8">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-800">
                        @forelse($services as $service)
                            <tr class="group border-l-4 border-l-transparent hover:border-l-[#0056D2] hover:bg-slate-50/40 transition-all duration-200">
                                <td class="py-4 px-6 text-center text-slate-400 text-xs font-semibold">
                                    {{ $services->firstItem() + $loop->index }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3.5">
                                        @if($service->image)
                                            <div class="w-12 h-12 rounded-2xl border border-slate-200/70 overflow-hidden bg-slate-50 flex items-center justify-center p-1.5 shadow-sm transition-transform duration-300 group-hover:scale-105">
                                                <img src="{{ $service->image }}" class="w-full h-full object-contain rounded-xl" alt="{{ $service->name }}">
                                            </div>
                                        @else
                                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#0056D2]/10 to-[#0049b8]/5 text-[#0056D2] border border-slate-200 flex items-center justify-center font-bold text-xs tracking-wider shadow-sm transition-transform duration-300 group-hover:scale-105 uppercase">
                                                {{ strtoupper(substr($service->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.services.show', $service) }}" class="block text-slate-800 font-extrabold font-display hover:text-[#0056D2] transition-colors text-sm sm:text-base leading-tight">{{ $service->name }}</a>
                                            <span class="block text-[11px] text-slate-400 mt-1 truncate max-w-xs font-normal leading-normal">{{ $service->description ?? 'No description provided' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-extrabold rounded-full bg-blue-50 text-blue-600 border border-blue-200 uppercase tracking-wider whitespace-nowrap">
                                        <i data-lucide="layers" class="w-3 h-3"></i>
                                        {{ $service->fields_count }} Variants
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-extrabold rounded-full bg-amber-50/80 text-amber-700 border border-amber-200 uppercase tracking-wider whitespace-nowrap">
                                        <i data-lucide="tag" class="w-3 h-3"></i>
                                        {{ $service->prices_count }} Prices
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-400 text-xs font-semibold">
                                    {{ $service->created_at?->format('M d, Y') ?? 'N/A' }}
                                </td>
                                <td class="py-4 px-6">
                                    @if($service->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-extrabold bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-full uppercase tracking-wider whitespace-nowrap">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                            </span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-extrabold bg-rose-50 text-rose-500 border border-rose-200 rounded-full uppercase tracking-wider whitespace-nowrap">
                                            <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right pr-8">
                                    <div class="flex items-center justify-end gap-2.5">
                                        <!-- Configure -->
                                        <a href="{{ route('admin.services.show', $service) }}" 
                                           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold bg-[#0056D2]/5 hover:bg-[#0056D2] text-[#0056D2] hover:text-white rounded-xl shadow-sm hover:shadow hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-150">
                                            <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                                            Configure
                                        </a>

                                        <!-- Edit -->
                                        <button @click="
                                            editService = { 
                                                id: '{{ $service->id }}', 
                                                name: '{{ addslashes($service->name) }}', 
                                                description: '{{ addslashes($service->description) }}', 
                                                image: '{{ $service->image ?? '' }}', 
                                                isActive: {{ $service->is_active ? 'true' : 'false' }} 
                                            }; 
                                            editModalOpen = true;
                                        " class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl shadow-sm hover:shadow hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-150">
                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                            Edit
                                        </button>

                                        <!-- Delete -->
                                        <button type="button" 
                                                @click="confirmDelete('{{ $service->id }}', '{{ addslashes($service->name) }}')"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white border border-rose-100 hover:border-rose-500 rounded-xl shadow-sm hover:shadow hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-150">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            Delete
                                        </button>
                                        
                                        <form id="delete-form-{{ $service->id }}" action="{{ route('admin.services.destroy', $service) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400 text-sm font-medium">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                            <i data-lucide="server-off" class="w-6 h-6"></i>
                                        </div>
                                        <span>No services configured. Create one to get started!</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{ $services->withQueryString()->links('vendor.pagination.custom') }}
        </div>

        <!-- Add Service Modal -->
        <div x-show="addModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" @click="addModalOpen = false"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="addModalOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 p-8 transform transition-all overflow-hidden bg-grid-pattern">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                        <h3 class="text-lg font-extrabold text-slate-900 font-display">Add New Service</h3>
                        <button @click="addModalOpen = false" class="p-1.5 rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-all duration-200 hover:rotate-90">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data" id="addServiceForm">
                        @csrf
                        <div class="space-y-5">
                            <!-- Image Input -->
                            <div class="bg-slate-50/50 p-4 border border-dashed border-slate-200 rounded-2xl text-center hover:bg-slate-50/80 transition-colors">
                                <div class="mx-auto w-14 h-14 bg-white border border-slate-100 rounded-2xl flex items-center justify-center text-slate-400 mb-3 shadow-sm overflow-hidden">
                                    <i data-lucide="image" class="w-7 h-7 text-[#0056D2]/65"></i>
                                </div>
                                <div class="text-xs text-slate-400 mb-2 font-medium">Select a service logo (PNG, JPG, max 2MB)</div>
                                <label for="addImage" class="cursor-pointer inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-extrabold bg-white border border-slate-200 text-[#0056D2] hover:bg-[#0056D2] hover:text-white rounded-xl shadow-sm hover:shadow active:scale-[0.97] transition-all duration-200">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    Choose Icon File
                                </label>
                                <input type="file" name="image" id="addImage" class="hidden" accept="image/*" onchange="previewImage(this, 'add-preview-container', 'add-preview-img')">
                                <!-- Image Preview Container -->
                                <div id="add-preview-container" class="hidden mt-3 mx-auto w-14 h-14 bg-white border border-slate-100 rounded-2xl p-1 flex items-center justify-center shadow-sm">
                                    <img id="add-preview-img" src="" class="w-full h-full object-contain rounded-xl" alt="Upload Preview">
                                </div>
                            </div>

                            <!-- Name -->
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Service Name</label>
                                <input type="text" name="name" required placeholder="e.g., MTN VTU" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-2xl text-sm text-slate-800 font-semibold transition-all duration-200 placeholder:text-slate-400">
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Description</label>
                                <textarea name="description" rows="3" placeholder="Provide brief details about this service..." class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-2xl text-sm text-slate-800 font-semibold transition-all duration-200 placeholder:text-slate-400"></textarea>
                            </div>

                            <!-- Status Toggle -->
                            <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200/65 rounded-2xl shadow-sm">
                                <div>
                                    <span class="block text-sm font-bold text-slate-900">Active Status</span>
                                    <span class="block text-xs text-slate-400 mt-0.5">Control availability of this service in system.</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 mt-8 border-t border-slate-100 pt-5">
                            <button type="button" @click="addModalOpen = false" class="px-5 py-3 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-2xl transition-all duration-150">
                                Cancel
                            </button>
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 text-xs font-bold bg-gradient-to-r from-[#0056D2] to-[#0049b8] hover:from-[#37446b] hover:to-[#0056D2] text-white rounded-2xl shadow-md hover:shadow-lg transition-all duration-150">
                                Create Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Service Modal -->
        <div x-show="editModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" @click="editModalOpen = false"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="editModalOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 p-8 transform transition-all overflow-hidden bg-grid-pattern">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                        <h3 class="text-lg font-extrabold text-slate-900 font-display">Edit Service</h3>
                        <button @click="editModalOpen = false" class="p-1.5 rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-all duration-200 hover:rotate-90">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="`/admin/services/${editService.id}`" method="POST" enctype="multipart/form-data" id="editServiceForm">
                        @csrf
                        @method('PUT')
                        <div class="space-y-5">
                            <!-- Current Image Preview / Upload -->
                            <div class="bg-slate-50/50 p-4 border border-dashed border-slate-200 rounded-2xl text-center hover:bg-slate-50/80 transition-colors">
                                <div class="flex justify-center gap-4 mb-3">
                                    <!-- Current Image -->
                                    <div>
                                        <template x-if="editService.image">
                                            <div class="mx-auto w-14 h-14 bg-white border border-slate-100 rounded-2xl flex items-center justify-center p-1.5 shadow-sm overflow-hidden">
                                                <img :src="editService.image" class="w-full h-full object-contain rounded-xl" alt="Preview">
                                            </div>
                                        </template>
                                        <template x-if="!editService.image">
                                            <div class="mx-auto w-14 h-14 bg-white border border-slate-100 rounded-2xl flex items-center justify-center text-slate-400 shadow-sm">
                                                <i data-lucide="image" class="w-7 h-7 text-slate-400"></i>
                                            </div>
                                        </template>
                                        <span class="block text-xs text-slate-400 font-extrabold uppercase tracking-wider mt-1.5">Current Icon</span>
                                    </div>
                                    <!-- New Preview (JS-driven) -->
                                    <div id="edit-preview-container" class="hidden">
                                        <div class="mx-auto w-14 h-14 bg-white border border-slate-200 rounded-2xl p-1.5 flex items-center justify-center shadow-sm">
                                            <img id="edit-preview-img" src="" class="w-full h-full object-contain rounded-xl" alt="New Preview">
                                        </div>
                                        <span class="block text-xs text-emerald-500 font-extrabold uppercase tracking-wider mt-1.5">New Selection</span>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-400 mb-2 font-medium">Select a new logo icon if you want to replace it</div>
                                <label for="editImage" class="cursor-pointer inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-extrabold bg-white border border-slate-200 text-[#0056D2] hover:bg-[#0056D2] hover:text-white rounded-xl shadow-sm hover:shadow active:scale-[0.97] transition-all duration-200">
                                    <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                                    Browse Icon
                                </label>
                                <input type="file" name="image" id="editImage" class="hidden" accept="image/*" onchange="previewImage(this, 'edit-preview-container', 'edit-preview-img')">
                            </div>

                            <!-- Name -->
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Service Name</label>
                                <input type="text" name="name" :value="editService.name" required class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-2xl text-sm text-slate-800 font-semibold transition-all duration-200">
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Description</label>
                                <textarea name="description" rows="3" :value="editService.description" class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 focus:outline-none rounded-2xl text-sm text-slate-800 font-semibold transition-all duration-200"></textarea>
                            </div>

                            <!-- Status Toggle -->
                            <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200/65 rounded-2xl shadow-sm">
                                <div>
                                    <span class="block text-sm font-bold text-slate-900">Active Status</span>
                                    <span class="block text-xs text-slate-400 mt-0.5">Control availability of this service in system.</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" :checked="editService.isActive" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 mt-8 border-t border-slate-100 pt-5">
                            <button type="button" @click="editModalOpen = false" class="px-5 py-3 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-2xl transition-all duration-150">
                                Cancel
                            </button>
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 text-xs font-bold bg-gradient-to-r from-[#0056D2] to-[#0049b8] hover:from-[#37446b] hover:to-[#0056D2] text-white rounded-2xl shadow-md hover:shadow-lg transition-all duration-150">
                                Update Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    {{-- SweetAlert CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Image preview helper function
        function previewImage(input, containerId, imgId) {
            const container = document.getElementById(containerId);
            const img = document.getElementById(imgId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    container.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                container.classList.add('hidden');
                img.src = '';
            }
        }

        // Form confirmation helper
        function confirmAction(formId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Are you sure you want to perform this action?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0056D2',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, continue!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
            return false;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Intercept create/edit form submissions for custom SweetAlert confirmation
            const addForm = document.getElementById('addServiceForm');
            if(addForm) {
                addForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    confirmAction('addServiceForm');
                });
            }

            const editForm = document.getElementById('editServiceForm');
            if(editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    confirmAction('editServiceForm');
                });
            }
        });

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete the service "${name}". All associated service fields and custom pricing records will be permanently deleted!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
</x-app-layout>
