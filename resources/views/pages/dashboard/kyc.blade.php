<!-- Custom Styles for Scrollbars -->
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<!-- Viewport-Blocking KYC Modal Overlay -->
<div class="fixed inset-0 z-[9999] bg-slate-900/65 backdrop-blur-sm flex items-start justify-center p-4 sm:p-6 md:p-10 overflow-y-auto">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl max-w-2xl w-full p-6 sm:p-8 transform transition-all relative my-8 animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Modal Header -->
        <div class="mb-6 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-3 mb-2.5">
                <div class="p-2.5 bg-[#0056D2]/10 text-[#0056D2] rounded-xl">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 font-display">Complete Profile Setup</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">Please provide your details below to activate your account. This information is required before accessing the platform.</p>
        </div>

        @if (session('warning'))
            <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm flex gap-3 items-start animate-in fade-in slide-in-from-top-2 duration-200">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('kyc.submit') }}" class="space-y-5">
            @csrf

            <!-- Name Grid -->
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <x-input-label for="first_name" value="First Name" />
                    <x-text-input id="first_name" type="text" name="first_name" :value="old('first_name', auth()->user()->first_name)" required autofocus
                           class="rounded-xl" placeholder="John" />
                    @error('first_name')
                        <p class="text-xs text-red-600 mt-1.5 flex gap-1 items-center font-medium">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <div>
                    <x-input-label for="middle_name" value="Middle Name (Optional)" />
                    <x-text-input id="middle_name" type="text" name="middle_name" :value="old('middle_name', auth()->user()->middle_name)"
                           class="rounded-xl" placeholder="Alexander" />
                    @error('middle_name')
                        <p class="text-xs text-red-600 mt-1.5 flex gap-1 items-center font-medium">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <div>
                    <x-input-label for="last_name" value="Last Name" />
                    <x-text-input id="last_name" type="text" name="last_name" :value="old('last_name', auth()->user()->last_name)" required
                           class="rounded-xl" placeholder="Doe" />
                    @error('last_name')
                        <p class="text-xs text-red-600 mt-1.5 flex gap-1 items-center font-medium">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>
            </div>

            <div>
                <x-input-label for="phone" value="Phone Number" />
                <x-text-input id="phone" type="tel" name="phone" :value="old('phone', auth()->user()->phone)" required
                       class="rounded-xl" placeholder="e.g. 08012345678" />
                @error('phone')
                    <p class="text-xs text-red-600 mt-1.5 flex gap-1 items-center font-medium">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- State & LGA Searchable Selects Grid -->
            <div x-data="kycLocationSelector()" class="grid grid-cols-2 gap-4 relative">
                <!-- State Selector -->
                <div class="relative">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">State <span class="text-red-500">*</span></label>
                    <input type="hidden" name="state" :value="state" required>
                    
                    <button type="button" @click="toggleStateDropdown()" 
                            class="w-full flex items-center justify-between px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 outline-none transition-all duration-200 text-slate-800 text-sm text-left"
                            :class="stateOpen ? 'bg-white border-[#0056D2] ring-4 ring-[#0056D2]/10' : ''">
                        <span x-text="state || 'Select State'" :class="state ? 'text-slate-800' : 'text-slate-400'">Select State</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="stateOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="stateOpen" @click.away="stateOpen = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 right-0 z-50 mt-1.5 bg-white rounded-xl border border-slate-200 shadow-xl p-2.5 transform origin-top max-w-full">
                        <div class="relative mb-2">
                            <i data-lucide="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400"></i>
                            <x-text-input type="text" x-model="stateSearch" placeholder="Search state..."
                                   class="pl-9 pr-4 !py-2 bg-slate-50 focus:bg-white" />
                        </div>
                        <div class="max-h-40 overflow-y-auto space-y-0.5 custom-scrollbar">
                            <template x-for="item in filteredStates" :key="item">
                                <button type="button" @click="selectState(item)"
                                        class="w-full text-left px-3 py-1.5 rounded-lg text-sm transition-all duration-150"
                                        :class="state === item ? 'bg-[#0056D2] text-white font-semibold' : 'text-slate-700 hover:bg-slate-50'">
                                    <span x-text="item"></span>
                                </button>
                            </template>
                            <div x-show="filteredStates.length === 0" class="text-xs text-slate-400 text-center py-3">
                                No states found
                            </div>
                        </div>
                    </div>
                    @error('state')
                        <p class="text-xs text-red-600 mt-1.5 flex gap-1 items-center font-medium">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- LGA Selector -->
                <div class="relative">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">LGA <span class="text-red-500">*</span></label>
                    <input type="hidden" name="lga" :value="lga" required>
                    
                    <button type="button" @click="toggleLgaDropdown()" :disabled="!state"
                            class="w-full flex items-center justify-between px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#0056D2] focus:ring-4 focus:ring-[#0056D2]/10 outline-none transition-all duration-200 text-slate-800 text-sm text-left disabled:opacity-60 disabled:cursor-not-allowed"
                            :class="lgaOpen ? 'bg-white border-[#0056D2] ring-4 ring-[#0056D2]/10' : ''">
                        <span x-text="lga || (state ? 'Select LGA' : 'Select State first')" :class="lga ? 'text-slate-800' : 'text-slate-400'">Select LGA</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="lgaOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="lgaOpen" @click.away="lgaOpen = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 right-0 z-50 mt-1.5 bg-white rounded-xl border border-slate-200 shadow-xl p-2.5 transform origin-top max-w-full">
                        <div class="relative mb-2">
                            <i data-lucide="search" class="absolute left-3 top-2.5 w-4 h-4 text-slate-400"></i>
                            <x-text-input type="text" x-model="lgaSearch" placeholder="Search LGA..."
                                   class="pl-9 pr-4 !py-2 bg-slate-50 focus:bg-white" />
                        </div>
                        <div class="max-h-40 overflow-y-auto space-y-0.5 custom-scrollbar">
                            <template x-for="item in filteredLgas" :key="item">
                                <button type="button" @click="selectLga(item)"
                                        class="w-full text-left px-3 py-1.5 rounded-lg text-sm transition-all duration-150"
                                        :class="lga === item ? 'bg-[#0056D2] text-white font-semibold' : 'text-slate-700 hover:bg-slate-50'">
                                    <span x-text="item"></span>
                                </button>
                            </template>
                            <div x-show="filteredLgas.length === 0" class="text-xs text-slate-400 text-center py-3">
                                No LGAs found
                            </div>
                        </div>
                    </div>
                    @error('lga')
                        <p class="text-xs text-red-600 mt-1.5 flex gap-1 items-center font-medium">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>
            </div>

            <div>
                <x-input-label for="address" value="Residential Address" />
                <x-textarea-input id="address" name="address" rows="2" required
                          class="rounded-xl resize-none"
                          placeholder="Enter your street address, building number, etc.">{{ old('address', auth()->user()->address) }}</x-textarea-input>
                @error('address')
                    <p class="text-xs text-red-600 mt-1.5 flex gap-1 items-center font-medium">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Agreement Checkbox -->
            <div class="pt-1">
                <label class="relative flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox" name="agree" value="1" required class="peer sr-only">
                    <div class="h-5 w-5 shrink-0 rounded-md border border-slate-200 bg-slate-50 peer-checked:bg-[#0056D2] peer-checked:border-[#0056D2] transition-all duration-200 flex items-center justify-center text-white">
                        <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3px] scale-50 opacity-0 peer-checked:scale-100 peer-checked:opacity-100 transition-all duration-200"></i>
                    </div>
                    <span class="text-xs text-slate-500 leading-normal select-none group-hover:text-slate-700 transition">
                        I agree to the <a href="#" class="text-[#0056D2] font-semibold hover:underline">Service Agreement</a> and confirm that all information provided is correct.
                    </span>
                </label>
                @error('agree')
                    <p class="text-xs text-red-600 mt-1.5 flex gap-1 items-center font-medium">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <x-primary-button type="submit" class="w-full font-display">
                <span>Complete Registration Setup</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </x-primary-button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-400">Need help? Contact support</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition py-1.5 px-3 rounded-lg hover:bg-slate-50 font-display">
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Nigeria State/LGA JS Mapping -->
<script>
    const stateLgaData = {
        "Abia": ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala Ngwa North", "Isiala Ngwa South", "Isuikwuato", "Obi Ngwa", "Ohafia", "Osisioma", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umu Nneochi"],
        "Adamawa": ["Demsa", "Fufure", "Ganye", "Gayuk", "Gombi", "Girei", "Hong", "Jada", "Lamurde", "Madagali", "Maiha", "Mayo Belwa", "Michika", "Mubi North", "Mubi South", "Numan", "Shelleng", "Song", "Toungo", "Yola North", "Yola South"],
        "Akwa Ibom": ["Abak", "Eastern Obolo", "Eket", "Esit Eket", "Essien Udim", "Etim Ekpo", "Etinan", "Ibeno", "Ibesikpo Asutan", "Ibiono-Ibom", "Ika", "Ikono", "Ikot Abasi", "Ikot Ekpene", "Ini", "Itu", "Mbo", "Mkpat-Enin", "Nsit-Atai", "Nsit-Ibom", "Nsit-Ubium", "Obot Akara", "Okobo", "Onna", "Oron", "Oruk Anam", "Udung-Uko", "Ukanafun", "Uruan", "Urue-Offong/Oruko", "Uyo"],
        "Anambra": ["Aguata", "Anambra East", "Anambra West", "Anaocha", "Awka North", "Awka South", "Ayamelum", "Dunukofia", "Ekwusigo", "Idemili North", "Idemili South", "Ihiala", "Njikoka", "Nnewi North", "Nnewi South", "Ogbaru", "Onitsha North", "Onitsha South", "Orumba North", "Orumba South", "Oyi"],
        "Bauchi": ["Alkaleri", "Bauchi", "Bogoro", "Damban", "Darazo", "Dass", "Gamawa", "Ganjuwa", "Giade", "Itas/Gadau", "Jama'are", "Katagum", "Kirfi", "Misau", "Ningi", "Shira", "Tafawa Balewa", "Toro", "Warji", "Zaki"],
        "Bayelsa": ["Brass", "Ekeremor", "Kolokuma/Opokuma", "Nembe", "Ogbia", "Sagbama", "Southern Ijaw", "Yenagoa"],
        "Benue": ["Agatu", "Apa", "Ado", "Buruku", "Gboko", "Guma", "Gwer East", "Gwer West", "Katsina-Ala", "Konshisha", "Kwande", "Logo", "Makurdi", "Obi", "Ogbadibo", "Ohimini", "Oju", "Okpokwu", "Oturkpo", "Tarka", "Ukum", "Ushongo", "Vandeikya"],
        "Borno": ["Abadam", "Askira/Uba", "Bama", "Bayo", "Biu", "Chibok", "Damboa", "Dikwa", "Gubio", "Guzamala", "Gwoza", "Hawul", "Jere", "Kaga", "Kala/Balge", "Konduga", "Kukawa", "Kwaya Kusar", "Mafa", "Magumeri", "Maiduguri", "Marte", "Mobbar", "Monguno", "Ngala", "Nganzai", "Shani"],
        "Cross River": ["Abi", "Akamkpa", "Akpabuyo", "Bakassi", "Bekwarra", "Biase", "Boki", "Calabar Municipal", "Calabar South", "Etung", "Ikom", "Obanliku", "Obubra", "Obudu", "Odukpani", "Ogoja", "Yakuur", "Yala"],
        "Delta": ["Aniocha North", "Aniocha South", "Bomadi", "Burutu", "Ethiope East", "Ethiope West", "Ika North East", "Ika South", "Isoko North", "Isoko South", "Ndokwa East", "Ndokwa West", "Okpe", "Oshimili North", "Oshimili South", "Patani", "Sapele", "Udu", "Ughelli North", "Ughelli South", "Ukwuani", "Uvwie", "Warri North", "Warri South", "Warri South West"],
        "Ebonyi": ["Abakaliki", "Afikpo North", "Afikpo South", "Ebonyi", "Ezza North", "Ezza South", "Ikwo", "Ishielu", "Ivo", "Izzi", "Ohaozara", "Ohaukwu", "Onicha"],
        "Edo": ["Akoko-Edo", "Egor", "Esan Central", "Esan North-East", "Esan Southeast", "Esan West", "Etsako Central", "Etsako East", "Etsako West", "Igueben", "Ikpoba Okha", "Orhionmwon", "Oredo", "Ovia Northeast", "Ovia Southwest", "Owan East", "Owan West", "Uhunmwonde"],
        "Ekiti": ["Ado Ekiti", "Efon", "Ekiti East", "Ekiti Southwest", "Ekiti West", "Emure", "Gbonyin", "Ido Osi", "Ijero", "Ikere", "Ikole", "Ilejemeje", "Irepodun/Ifelodun", "Ise/Orun", "Moba", "Oye"],
        "Enugu": ["Aninri", "Awgu", "Enugu East", "Enugu North", "Enugu South", "Ezeagu", "Igbo Etiti", "Igbo Eze North", "Igbo Eze South", "Isi Uzo", "Nkanu East", "Nkanu West", "Nsukka", "Oji River", "Udenu", "Udi", "Uzo-Uwani"],
        "FCT": ["Abaji", "Bwari", "Gwagwalada", "Kuje", "Kwali", "Municipal Area Council"],
        "Gombe": ["Akko", "Balanga", "Billiri", "Dukku", "Funakaye", "Gombe", "Kaltungo", "Kwami", "Nafada", "Shongom", "Yamaltu/Deba"],
        "Imo": ["Ahiazu Mbaise", "Ehime Mbano", "Ezinihitte", "Ideato North", "Ideato South", "Ihitte/Uboma", "Ikeduru", "Isiala Mbano", "Isu", "Mbaitoli", "Ngor Okpala", "Njaba", "Nkwerre", "Nwangele", "Obowo", "Oguta", "Ohaji/Egbema", "Okigwe", "Orlu", "Orsu", "Oru East", "Oru West", "Owerri Municipal", "Owerri North", "Owerri West", "Unuimo"],
        "Jigawa": ["Auyo", "Babura", "Biriniwa", "Birnin Kudu", "Buji", "Dutse", "Gagarawa", "Garki", "Gumel", "Guri", "Gwaram", "Gwiwa", "Hadejia", "Jahun", "Kafur", "Kaugama", "Kazaure", "Kiri Kasama", "Kiyawa", "Maigatari", "Malam Madori", "Miga", "Ringim", "Roni", "Sule Tankarkar", "Taura", "Yankwashi"],
        "Kaduna": ["Birnin Gwari", "Chikun", "Giwa", "Kajuru", "Igabi", "Ikara", "Jaba", "Jema'a", "Kachia", "Kaduna North", "Kaduna South", "Kagarko", "Kaura", "Kauru", "Kubau", "Kudan", "Lere", "Makarfi", "Sabon Gari", "Sanga", "Soba", "Zangon Kataf", "Zaria"],
        "Kano": ["Ajingi", "Albasu", "Bagwai", "Bebeji", "Bichi", "Bunkure", "Dala", "Dambatta", "Dawakin Kudu", "Dawakin Tofa", "Doguwa", "Fagge", "Gabasawa", "Garko", "Garun Mallam", "Gaya", "Gezawa", "Gwale", "Gwarzo", "Kabo", "Karaye", "Kibiya", "Kiru", "Kumbotso", "Kunchi", "Kura", "Madobi", "Makoda", "Minjibir", "Nasarawa", "Rano", "Rimin Gado", "Rogo", "Shanono", "Sumaila", "Takai", "Tarauni", "Tofa", "Tsanyawa", "Tudun Wada", "Ungogo", "Warawa", "Wudil"],
        "Katsina": ["Bakori", "Batagarawa", "Batsari", "Baure", "Bindawa", "Charanchi", "Dandume", "Danja", "Dan Musa", "Daura", "Dutsin Ma", "Faskari", "Funtua", "Ingawa", "Jibia", "Kafur", "Kaita", "Kankara", "Kankia", "Kani", "Katsina", "Kurfi", "Kusada", "Mai'Adua", "Malumfashi", "Mani", "Mashi", "Musawa", "Rimi", "Sabuwa", "Safana", "Sandamu", "Zango"],
        "Kebbi": ["Aleiro", "Arewa Dandi", "Argungu", "Augie", "Bagudo", "Birnin Kebbi", "Bunza", "Dandi", "Fakai", "Gwandu", "Jega", "Kalgo", "Koko/Besse", "Maiyama", "Ngaski", "Sakaba", "Shanga", "Suru", "Wasagu/Danko", "Yauri", "Zuru"],
        "Kogi": ["Adavi", "Ajaokuta", "Ankpa", "Bassa", "Dekina", "Ibaji", "Idah", "Igalamela Odolu", "Ijumu", "Kabba/Bunu", "Kogi", "Lokoja", "Mopa Muro", "Ofu", "Ogori/Magongo", "Okehi", "Okene", "Olamaboro", "Omala", "Yagba East", "Yagba West"],
        "Kwara": ["Asa", "Baruten", "Edu", "Ekiti", "Ilorin East", "Ilorin South", "Ilorin West", "Irepodun", "Isin", "Kaiama", "Moro", "Offa", "Oke Ero", "Oyun", "Pategi"],
        "Lagos": ["Agege", "Ajeromi-Ifelodun", "Alimosho", "Amuwo-Odofin", "Apapa", "Badagry", "Epe", "Eti Osa", "Ibeju-Lekki", "Ifako-Ijaiye", "Ikeja", "Ikorodu", "Kosofe", "Lagos Island", "Lagos Mainland", "Mushin", "Ojo", "Oshodi-Isolo", "Shomolu", "Surulere"],
        "Nasarawa": ["Akwanga", "Awe", "Doma", "Karu", "Keana", "Keffi", "Kokona", "Lafia", "Nasarawa", "Nasarawa Egon", "Obi", "Toto", "Wamba"],
        "Niger": ["Agaie", "Agwara", "Bida", "Borgu", "Bosso", "Chanchaga", "Edati", "Gbako", "Gurara", "Katcha", "Kontagora", "Lapai", "Lavun", "Magama", "Mariga", "Mashegu", "Mokwa", "Moya", "Paikoro", "Rafi", "Rijau", "Shiroro", "Suleja", "Tafa", "Wushishi"],
        "Ogun": ["Abeokuta North", "Abeokuta South", "Ado-Odo/Ota", "Egbado North", "Egbado South", "Ewekoro", "Ifo", "Ijebu East", "Ijebu North", "Ijebu North East", "Ijebu Ode", "Ikenne", "Imeko Afon", "Ipokia", "Obafemi Owode", "Odeda", "Odogbolu", "Ogun Waterside", "Remo North", "Shagamu"],
        "Ondo": ["Akoko North-East", "Akoko North-West", "Akoko South-East", "Akoko South-West", "Ese Odo", "Idanre", "Ifedore", "Ilaje", "Ile Oluji/Okeigbo", "Irele", "Odigbo", "Okitipupa", "Ondo East", "Ondo West", "Ose", "Owo"],
        "Osun": ["Atakunmosa East", "Atakunmosa West", "Aiyedaade", "Aiyedire", "Boluwaduro", "Boripe", "Ede North", "Ede South", "Ife Central", "Ife East", "Ife North", "Ife South", "Egbedore", "Ejigbo", "Ila", "Ilesa East", "Ilesa West", "Irepodun", "Irewole", "Isokan", "Iwo", "Obokun", "Odo Otin", "Ola Oluwa", "Olorunda", "Oriade", "Orolu", "Osogbo"],
        "Oyo": ["Afijio", "Akinyele", "Atiba", "Atisbo", "Egbeda", "Ibadan North", "Ibadan North-East", "Ibadan North-West", "Ibadan South-East", "Ibadan South-West", "Ibarapa Central", "Ibarapa East", "Ibarapa North", "Ido", "Irepo", "Iseyin", "Itesiwaju", "Iwajowa", "Kajola", "Lagelu", "Ogbomosho North", "Ogbomosho South", "Ogo Oluwa", "Olorunsogo", "Oluyole", "Ona Ara", "Orelope", "Ori Ire", "Oyo East", "Oyo West", "Saki East", "Saki West", "Surulere"],
        "Plateau": ["Bokkos", "Barkin Ladi", "Bassa", "Jos East", "Jos North", "Jos South", "Kanam", "Kanke", "Langtang North", "Langtang South", "Mangu", "Mikang", "Pankshin", "Qua'an Pan", "Riyom", "Shendam", "Wase"],
        "Rivers": ["Abua/Odual", "Ahoada East", "Ahoada West", "Akuku Toru", "Andoni", "Asari-Toru", "Bonny", "Degema", "Eleme", "Emuoha", "Etche", "Gokana", "Ikwerre", "Khana", "Obio/Akpor", "Ogba/Egbema/Ndoni", "Ogu/Bolo", "Okrika", "Omuma", "Opobo/Nkoro", "Oyigbo", "Port Harcourt", "Tai"],
        "Sokoto": ["Binji", "Bodinga", "Dange Shuni", "Gada", "Goronyo", "Gudu", "Gwadabawa", "Illela", "Isa", "Kebbe", "Kware", "Rabah", "Sabon Birni", "Shagari", "Silame", "Sokoto North", "Sokoto South", "Tambuwal", "Tangaza", "Tureta", "Wamako", "Wurno", "Yabo"],
        "Taraba": ["Ardo Kola", "Bali", "Donga", "Gashaka", "Gassol", "Ibi", "Jalingo", "Karim Lamido", "Kumi", "Lau", "Sardauna", "Takum", "Ussa", "Wukari", "Yorro", "Zing"],
        "Yobe": ["Bade", "Bursari", "Damaturu", "Fika", "Fune", "Geidam", "Gujba", "Gulani", "Jakusko", "Karasuwa", "Machina", "Nangere", "Nguru", "Potiskum", "Tarmuwa", "Yunusari", "Yusufari"],
        "Zamfara": ["Anka", "Bakura", "Birnin Magaji/Kiyaw", "Bukkuyum", "Bungudu", "Gummi", "Gusau", "Kaura Namoda", "Maradun", "Maru", "Shinkafi", "Talata Mafara", "Zurmi"]
    };

    window.kycLocationSelector = function() {
        return {
            stateOpen: false,
            lgaOpen: false,
            stateSearch: '',
            lgaSearch: '',
            state: "{{ old('state', auth()->user()->state ?? '') }}",
            lga: "{{ old('lga', auth()->user()->lga ?? '') }}",
            states: Object.keys(stateLgaData).sort(),
            get filteredStates() {
                if (!this.stateSearch) return this.states;
                return this.states.filter(s => s.toLowerCase().includes(this.stateSearch.toLowerCase()));
            },
            get filteredLgas() {
                const list = stateLgaData[this.state] || [];
                if (!this.lgaSearch) return list.slice().sort();
                return list.filter(l => l.toLowerCase().includes(this.lgaSearch.toLowerCase())).sort();
            },
            toggleStateDropdown() {
                this.stateOpen = !this.stateOpen;
                if (this.stateOpen) {
                    this.lgaOpen = false;
                    this.stateSearch = '';
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            },
            toggleLgaDropdown() {
                if (!this.state) return;
                this.lgaOpen = !this.lgaOpen;
                if (this.lgaOpen) {
                    this.stateOpen = false;
                    this.lgaSearch = '';
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            },
            selectState(name) {
                this.state = name;
                this.lga = '';
                this.stateOpen = false;
            },
            selectLga(name) {
                this.lga = name;
                this.lgaOpen = false;
            }
        };
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Initial icon load for non-dynamic icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
