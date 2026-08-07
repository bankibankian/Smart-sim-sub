<x-app-layout>
    <title>SmartSIM - Admin SIM Management</title>

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold font-display text-slate-900 flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center text-[#0056D2] border border-indigo-100/50 shadow-sm">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                    </div>
                    SIM Cards & Plans Management
                </h1>
                <p class="text-sm text-slate-500 mt-1">Upload available SIM numbers, assign numbers directly to roles, and approve user requests.</p>
            </div>
        </div>

        <!-- Header Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Total Uploaded -->
            <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100/50 flex items-center justify-center text-[#0056D2] shrink-0">
                    <i data-lucide="database" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Total Uploaded</span>
                    <span class="text-xl font-extrabold text-slate-800 font-display mt-0.5">{{ $totalUploaded }}</span>
                </div>
            </div>

            <!-- Card 2: Total Available -->
            <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100/50 flex items-center justify-center text-blue-600 shrink-0">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Not Assigned</span>
                    <span class="text-xl font-extrabold text-slate-800 font-display mt-0.5">{{ $totalAvailable }}</span>
                </div>
            </div>

            <!-- Card 3: Total Assigned -->
            <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 border border-purple-100/50 flex items-center justify-center text-purple-600 shrink-0">
                    <i data-lucide="user-check" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Total Assigned</span>
                    <span class="text-xl font-extrabold text-slate-800 font-display mt-0.5">{{ $totalAssigned }}</span>
                </div>
            </div>

            <!-- Card 4: Total Activated -->
            <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100/50 flex items-center justify-center text-emerald-600 shrink-0">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Total Activated</span>
                    <span class="text-xl font-extrabold text-slate-800 font-display mt-0.5">{{ $totalActivated }}</span>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-emerald-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif
        @if (session('warning'))
            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 text-amber-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('warning') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-rose-50 border border-rose-100 rounded-2xl p-4 text-rose-800 flex items-start gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-500 shrink-0 mt-0.5"></i>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        <!-- Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Side: Upload Numbers Form -->
            <div class="lg:col-span-4 space-y-6" x-data="{ uploadTab: 'manual' }">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100/50 flex items-center justify-center text-[#0056D2]">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-slate-800 font-display">Add Available Numbers</h3>
                            <p class="text-xs text-slate-400">Add inventory numbers for users to request.</p>
                        </div>
                    </div>

                    <!-- Upload Toggle Tabs -->
                    <div class="flex bg-slate-50 p-1 rounded-xl mb-4 text-xs font-semibold">
                        <button type="button" @click="uploadTab = 'manual'" :class="uploadTab === 'manual' ? 'bg-[#0056D2] text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-1.5 rounded-lg transition-all text-center">Manual Text</button>
                        <button type="button" @click="uploadTab = 'excel'" :class="uploadTab === 'excel' ? 'bg-[#0056D2] text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-1.5 rounded-lg transition-all flex items-center justify-center gap-1">
                            <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i> Excel Sheet
                        </button>
                    </div>

                    <!-- Manual Text Form -->
                    <form x-show="uploadTab === 'manual'" action="{{ route('admin.sim-plan.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-1.5">
                            <label for="add_category" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">SIM Category</label>
                            <select id="add_category" name="category" required class="w-full py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0056D2]/20 focus:border-[#0056D2] text-slate-700 font-medium">
                                <option value="">Select Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="add_provider" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Network Operator</label>
                            <select id="add_provider" name="provider" required class="w-full py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0056D2]/20 focus:border-[#0056D2] text-slate-700 font-medium">
                                <option value="">Select Network</option>
                                @foreach ($providers as $prov)
                                    <option value="{{ $prov }}">{{ strtoupper($prov) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="add_numbers" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">SIM Numbers</label>
                            <textarea id="add_numbers" name="numbers" rows="6" required placeholder="08030000000&#10;08031111111&#10;08032222222"
                                      class="w-full py-2 px-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0056D2]/20 focus:border-[#0056D2] text-slate-800 font-semibold placeholder:font-normal placeholder:text-slate-400"></textarea>
                            <p class="text-[10px] text-slate-400">Separate numbers by comma or new lines.</p>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition-all duration-200 flex items-center justify-center gap-2">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Upload Numbers
                        </button>
                    </form>

                    <!-- Excel Upload Form -->
                    <form x-show="uploadTab === 'excel'" style="display: none;" action="{{ route('admin.sim-plan.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="bg-indigo-50/50 rounded-2xl p-4 border border-indigo-100/50 space-y-2">
                            <h4 class="text-xs font-bold text-[#0056D2] flex items-center gap-1.5">
                                <i data-lucide="info" class="w-4 h-4 text-indigo-500"></i> Excel Upload Instructions
                            </h4>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Columns in your Excel file must include exactly: <strong class="text-indigo-800">number</strong>, <strong class="text-indigo-800">category</strong>, and <strong class="text-indigo-800">provider</strong> headers in row 1.
                            </p>
                            <a href="{{ route('admin.sim-plan.download-sample') }}" class="text-[11px] font-bold text-indigo-600 hover:underline flex items-center gap-1 mt-1">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download Sample Excel Template
                            </a>
                        </div>

                        <div class="space-y-2">
                            <label for="excel_file" class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Choose Excel/CSV file</label>
                            <div class="border-2 border-dashed border-slate-200 hover:border-[#0056D2]/50 transition-colors rounded-2xl p-4 text-center cursor-pointer relative">
                                <input type="file" id="excel_file" name="excel_file" required accept=".xlsx,.xls,.csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="document.getElementById('excel-file-name').innerText = this.files[0].name;">
                                <div class="space-y-1.5">
                                    <i data-lucide="file-up" class="w-8 h-8 text-slate-400 mx-auto"></i>
                                    <p class="text-xs font-semibold text-slate-600" id="excel-file-name">Click or drag Excel file here</p>
                                    <p class="text-[10px] text-slate-400">Supports .xlsx, .xls, .csv up to 5MB</p>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all duration-200 flex items-center justify-center gap-2">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            Import Excel SIMs
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Side: Requests & Inventory Tabs -->
            <div class="lg:col-span-8 space-y-6" x-data="{ currentTab: 'requests' }">
                <!-- Navigation Tabs -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-2 flex gap-1">
                    <button type="button" @click="currentTab = 'requests'" :class="currentTab === 'requests' ? 'bg-[#0056D2] text-white' : 'text-slate-500 hover:bg-slate-50'" class="flex-1 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
                        <i data-lucide="inbox" class="w-4 h-4"></i> Pending Requests ({{ count($pendingRequests) }})
                    </button>
                    <button type="button" @click="currentTab = 'resolved'" :class="currentTab === 'resolved' ? 'bg-[#0056D2] text-white' : 'text-slate-500 hover:bg-slate-50'" class="flex-1 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
                        <i data-lucide="check-square" class="w-4 h-4"></i> Resolved Requests
                    </button>
                    <button type="button" @click="currentTab = 'inventory'" :class="currentTab === 'inventory' ? 'bg-[#0056D2] text-white' : 'text-slate-500 hover:bg-slate-50'" class="flex-1 py-2 text-xs font-bold rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5">
                        <i data-lucide="database" class="w-4 h-4"></i> SIM Inventory
                    </button>
                </div>

                <!-- Tab: Pending Requests -->
                <div x-show="currentTab === 'requests'" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <h3 class="font-bold text-slate-800 font-display pb-3 border-b border-slate-100">Pending Actions</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase">
                                    <th class="py-2.5">User</th>
                                    <th class="py-2.5">Number</th>
                                    <th class="py-2.5">Type</th>
                                    <th class="py-2.5">Category/Provider</th>
                                    <th class="py-2.5">Amount</th>
                                    <th class="py-2.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendingRequests as $req)
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3">
                                            <span class="font-bold text-slate-700">{{ $req->user->first_name }} {{ $req->user->last_name }}</span>
                                            <span class="block text-[10px] text-slate-400">{{ $req->user->role }}</span>
                                        </td>
                                        <td class="py-3 font-semibold text-slate-800">{{ $req->number }}</td>
                                        <td class="py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $req->request_type === 'activation' ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-[#0056D2]' }}">
                                                {{ $req->request_type }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <span class="font-medium text-slate-600 block">{{ $req->category }}</span>
                                            <span class="text-[10px] text-slate-400 block uppercase">{{ $req->provider }}</span>
                                        </td>
                                        <td class="py-3">
                                            <span class="font-bold text-slate-700">₦{{ number_format($req->amount, 2) }}</span>
                                        </td>
                                        <td class="py-3 text-right space-x-1 whitespace-nowrap">
                                            <form action="{{ route('admin.sim-plan.requests.approve', $req->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-1 rounded-lg transition-colors">Approve</button>
                                            </form>
                                            <button type="button" @click="Swal.fire({
                                                title: 'Reject Request',
                                                input: 'text',
                                                inputLabel: 'Rejection Reason',
                                                inputPlaceholder: 'Enter reason here...',
                                                showCancelButton: true,
                                                confirmButtonColor: '#e11d48',
                                                cancelButtonColor: '#64748b',
                                                confirmButtonText: 'Reject'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    let f = document.createElement('form');
                                                    f.action = '{{ route('admin.sim-plan.requests.reject', $req->id) }}';
                                                    f.method = 'POST';
                                                    f.innerHTML = '<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'><input type=\'hidden\' name=\'admin_notes\' value=\'' + (result.value || '') + '\'>';
                                                    document.body.appendChild(f);
                                                    f.submit();
                                                }
                                            })" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-2.5 py-1 rounded-lg transition-colors">
                                                Reject
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 font-semibold">No pending requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Resolved Requests -->
                <div x-show="currentTab === 'resolved'" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <h3 class="font-bold text-slate-800 font-display pb-3 border-b border-slate-100">Resolved Requests Log</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase">
                                    <th class="py-2.5">User</th>
                                    <th class="py-2.5">Number</th>
                                    <th class="py-2.5">Type</th>
                                    <th class="py-2.5">Amount</th>
                                    <th class="py-2.5">Status</th>
                                    <th class="py-2.5">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($resolvedRequests as $req)
                                    <tr class="border-b border-slate-50">
                                        <td class="py-3">
                                            <span class="font-bold text-slate-700">{{ $req->user->first_name }} {{ $req->user->last_name }}</span>
                                            <span class="block text-[10px] text-slate-400">{{ $req->user->role }}</span>
                                        </td>
                                        <td class="py-3 font-semibold text-slate-800">{{ $req->number }}</td>
                                        <td class="py-3 font-semibold capitalize">{{ $req->request_type }}</td>
                                        <td class="py-3 font-bold text-slate-700">₦{{ number_format($req->amount, 2) }}</td>
                                        <td class="py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $req->status === 'approved' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                                {{ $req->status }}
                                            </span>
                                            @if ($req->admin_notes)
                                                <span class="block text-[9px] text-slate-400 italic mt-0.5">{{ $req->admin_notes }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-slate-400 font-medium">{{ $req->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 font-semibold">No resolved requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $resolvedRequests->withQueryString()->links('vendor.pagination.custom') }}
                </div>

                <!-- Tab: SIM Inventory -->
                <div x-show="currentTab === 'inventory'" 
                     x-data="{
                        selectedSims: [],
                        maxSelect: 50,
                        toggleAll(checked) {
                            if (checked) {
                                let ids = Array.from(document.querySelectorAll('.sim-checkbox:not(:disabled)')).map(el => parseInt(el.value));
                                this.selectedSims = ids.slice(0, this.maxSelect);
                            } else {
                                this.selectedSims = [];
                            }
                        },
                        toggleSim(id, checked) {
                            if (checked) {
                                if (this.selectedSims.length >= this.maxSelect) {
                                    Swal.fire('Limit Exceeded', 'You can only select up to 50 numbers at a time.', 'warning');
                                    document.getElementById('sim_cb_' + id).checked = false;
                                    return;
                                }
                                if (!this.selectedSims.includes(id)) {
                                    this.selectedSims.push(id);
                                }
                            } else {
                                this.selectedSims = this.selectedSims.filter(x => x !== id);
                            }
                        }
                     }"
                     class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-3 border-b border-slate-100">
                        <h3 class="font-bold text-slate-800 font-display">SIM Inventory pool</h3>
                    </div>

                    <!-- Filter Form -->
                    <form action="{{ route('admin.sim-plan.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-5 gap-3 bg-slate-50 p-3 rounded-2xl">
                        <div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Number..." 
                                   class="w-full text-xs py-2 px-3 rounded-xl border border-slate-200 focus:outline-none text-slate-800">
                        </div>
                        <div>
                            <select name="category" class="w-full text-xs py-2 px-3 rounded-xl border border-slate-200 text-slate-700">
                                <option value="">Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="provider" class="w-full text-xs py-2 px-3 rounded-xl border border-slate-200 text-slate-700">
                                <option value="">Network</option>
                                @foreach ($providers as $prov)
                                    <option value="{{ $prov }}" {{ request('provider') === $prov ? 'selected' : '' }}>{{ strtoupper($prov) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="status" class="w-full text-xs py-2 px-3 rounded-xl border border-slate-200 text-slate-700">
                                <option value="">Status</option>
                                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>NOT ASSIGNED</option>
                                <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>ASSIGNED</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>ACTIVATED</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-[#0056D2] hover:bg-[#354062] text-white font-bold text-xs py-2 px-3 rounded-xl">Filter</button>
                            <a href="{{ route('admin.sim-plan.index') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs py-2 px-3 rounded-xl flex items-center justify-center">Reset</a>
                        </div>
                    </form>

                    <!-- Bulk Assign Action Bar -->
                    <div x-show="selectedSims.length > 0" 
                         style="display: none;" 
                         class="flex items-center justify-between bg-indigo-50/80 border border-indigo-100 rounded-2xl p-3.5 animate-in slide-in-from-top duration-200">
                        <span class="text-xs font-semibold text-slate-700">
                            Selected: <span class="font-extrabold text-indigo-700" x-text="selectedSims.length"></span> / 50 SIMs
                        </span>
                        <button type="button" @click="Swal.fire({
                            title: 'Bulk Assign SIMs',
                            html: `
                                <div class='text-left space-y-2'>
                                    <label class='text-xs font-bold text-slate-500 uppercase block'>Select Assignee</label>
                                    <select id='bulk_user_id' class='w-full py-2.5 border rounded-xl font-medium text-slate-700'>
                                        <option value=''>Select Account</option>
                                        @foreach ($assignableUsers as $au)
                                            <option value='{{ $au->id }}'>{{ $au->first_name }} {{ $au->last_name }} ({{ $au->role }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonColor: '#0056D2',
                            confirmButtonText: 'Assign Selected',
                            preConfirm: () => {
                                const val = document.getElementById('bulk_user_id').value;
                                if (!val) {
                                    Swal.showValidationMessage('Please select a user');
                                }
                                return val;
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                let f = document.createElement('form');
                                f.action = '{{ route('admin.sim-plan.assign') }}';
                                f.method = 'POST';
                                let inputs = '<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'><input type=\'hidden\' name=\'user_id\' value=\'' + result.value + '\'>';
                                selectedSims.forEach(id => {
                                    inputs += '<input type=\'hidden\' name=\'sim_ids[]\' value=\'' + id + '\'>';
                                });
                                f.innerHTML = inputs;
                                document.body.appendChild(f);
                                f.submit();
                            }
                        })" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2 px-4 rounded-xl flex items-center gap-1.5 transition-colors shadow-sm">
                            <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                            Assign Checked
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase">
                                    <th class="py-2.5 pl-3 w-8">
                                        <input type="checkbox" @change="toggleAll($event.target.checked)" class="rounded text-[#0056D2] focus:ring-[#0056D2]">
                                    </th>
                                    <th class="py-2.5">Number</th>
                                    <th class="py-2.5">Category/Network</th>
                                    <th class="py-2.5">Status</th>
                                    <th class="py-2.5">Assigned To</th>
                                    <th class="py-2.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sims as $sim)
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                                        <td class="py-3 pl-3">
                                            @if ($sim->status === 'available')
                                                <input type="checkbox" id="sim_cb_{{ $sim->id }}" value="{{ $sim->id }}" 
                                                       :checked="selectedSims.includes({{ $sim->id }})"
                                                       @change="toggleSim({{ $sim->id }}, $event.target.checked)"
                                                       class="sim-checkbox rounded text-[#0056D2] focus:ring-[#0056D2]">
                                            @else
                                                <input type="checkbox" disabled class="rounded bg-slate-50 border-slate-200 text-slate-350 cursor-not-allowed">
                                            @endif
                                        </td>
                                        <td class="py-3 font-bold text-slate-800">{{ $sim->number }}</td>
                                        <td class="py-3">
                                            <span class="font-semibold text-slate-700 block">{{ $sim->category }}</span>
                                            <span class="text-[10px] text-slate-400 block uppercase">{{ $sim->provider }}</span>
                                        </td>
                                        <td class="py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase 
                                                {{ $sim->status === 'active' ? 'bg-emerald-50 text-emerald-600' : ($sim->status === 'assigned' ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-600') }}">
                                                @if($sim->status === 'active')
                                                    ACTIVATED
                                                @elseif($sim->status === 'available')
                                                    NOT ASSIGNED
                                                @else
                                                    {{ $sim->status }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            @if ($sim->user)
                                                <span class="font-semibold text-slate-700">{{ $sim->user->first_name }} {{ $sim->user->last_name }}</span>
                                                <span class="block text-[10px] text-slate-400 capitalize">{{ $sim->user->role }}</span>
                                            @else
                                                <span class="text-slate-400 font-semibold italic">None</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-right space-x-1 whitespace-nowrap">
                                            @if ($sim->status === 'available')
                                                <button type="button" @click="Swal.fire({
                                                    title: 'Assign SIM Card',
                                                    html: `
                                                        <div class='text-left space-y-2'>
                                                            <label class='text-xs font-bold text-slate-500 uppercase'>Select Assignee</label>
                                                            <select id='swal_user_id' class='w-full py-2.5 border rounded-xl font-medium text-slate-700'>
                                                                <option value=''>Select Account</option>
                                                                @foreach ($assignableUsers as $au)
                                                                    <option value='{{ $au->id }}'>{{ $au->first_name }} {{ $au->last_name }} ({{ $au->role }})</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    `,
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#0056D2',
                                                    confirmButtonText: 'Assign Now',
                                                    preConfirm: () => {
                                                        const val = document.getElementById('swal_user_id').value;
                                                        if (!val) {
                                                            Swal.showValidationMessage('Please select a user');
                                                        }
                                                        return val;
                                                    }
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        let f = document.createElement('form');
                                                        f.action = '{{ route('admin.sim-plan.assign') }}';
                                                        f.method = 'POST';
                                                        f.innerHTML = '<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'><input type=\'hidden\' name=\'sim_ids[]\' value=\'{{ $sim->id }}\'><input type=\'hidden\' name=\'user_id\' value=\'' + result.value + '\'>';
                                                        document.body.appendChild(f);
                                                        f.submit();
                                                    }
                                                })" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold bg-[#0056D2] hover:bg-[#354062] text-white rounded-xl transition-all duration-150 shadow-sm">
                                                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                                    Assign
                                                </button>
                                            @else
                                                <form action="{{ route('admin.sim-plan.unassign', $sim->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all duration-150">
                                                        <i data-lucide="user-minus" class="w-3.5 h-3.5"></i>
                                                        Unassign
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400 font-semibold">No SIM records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $sims->withQueryString()->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
