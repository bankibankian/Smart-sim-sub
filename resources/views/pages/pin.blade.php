<!-- Custom Tailwind PIN Modal -->
<div id="pinModal" 
     x-data="{ show: false }" 
     x-show="show" 
     @open-modal.window="show = true" 
     @close-modal.window="show = false"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
     style="display: none;">

    <!-- Modal Card -->
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         @click.away="show = false"
         class="relative w-full max-w-md bg-white rounded-3xl border border-slate-100 shadow-2xl overflow-hidden flex flex-col">
        
        {{-- Modal Header --}}
        <div class="bg-gradient-to-r from-[#0056D2] to-[#0049b8] px-6 py-5 border-b border-slate-100 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/10">
                    <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h5 class="font-bold font-display" id="modalTitle">Confirm Transaction</h5>
                    <p class="text-xs text-slate-200 mt-0.5" id="modalSubtitle">Please review details carefully</p>
                </div>
            </div>
            <button type="button" @click="show = false" class="p-1.5 hover:bg-white/10 rounded-lg text-white/80 hover:text-white transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="p-6 bg-slate-50/50 space-y-5">
            
            {{-- Step 1: Confirmation Screen --}}
            <div id="confirmationStep" class="space-y-5">
                <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm space-y-3.5">
                    <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Recipient</span>
                        <span class="text-xs font-bold text-slate-800 text-right" id="confirmAccountName">N/A</span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Bank / Service</span>
                        <span class="text-xs font-semibold text-slate-800 text-right" id="confirmBankName">N/A</span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Account Number</span>
                        <span class="text-xs font-semibold text-slate-800 text-right font-mono" id="confirmAccountNo">N/A</span>
                    </div>
                    <div class="flex justify-between items-center pt-1.5">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Amount</span>
                        <span class="text-xl font-extrabold text-[#0056D2] font-display" id="confirmAmount">₦0.00</span>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 text-amber-800 flex items-start gap-3 shadow-sm">
                    <i data-lucide="info" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"></i>
                    <div class="text-[11px] leading-relaxed">
                        Verify the recipient account name and amount before confirming. Funds transferred to incorrect accounts are generally non-reversible.
                    </div>
                </div>

                <button type="button" id="btnGoToPin" class="w-full py-3.5 px-6 bg-[#0056D2] hover:bg-[#003a8c] text-white font-bold text-xs rounded-xl shadow-md shadow-[#0056D2]/10 hover:shadow-[#0056D2]/20 active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2">
                    Confirm & Pay
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Step 2: PIN Authorization Screen --}}
            <div id="pinStep" class="hidden space-y-5">
                <div class="text-center space-y-2">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 text-[#0056D2] flex items-center justify-center mx-auto shadow-inner">
                        <i data-lucide="key-round" class="w-5 h-5"></i>
                    </div>
                    <h6 class="font-bold text-slate-800 text-sm">Enter Security PIN</h6>
                    <p class="text-xs text-slate-400 leading-relaxed px-6">Provide your 5-digit transaction PIN to authorize this transfer.</p>
                </div>

                <div class="flex justify-center">
                    <input type="password" id="pinInput" class="w-full max-w-[220px] h-14 text-center py-2 px-3 font-extrabold text-slate-700 bg-white border-2 border-slate-200 focus:border-[#0056D2] focus:ring-1 focus:ring-[#0056D2] rounded-xl focus:outline-none transition-all" 
                           maxlength="5" placeholder="•••••"
                           style="font-size: 2rem; letter-spacing: 0.8rem;">
                </div>

                {{-- Error Display --}}
                <div id="pinError" class="bg-rose-50 border border-rose-100 rounded-xl p-3 text-rose-800 hidden shadow-sm animate-in fade-in duration-200">
                    <div class="flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0"></i>
                        <span class="text-xs font-semibold" id="pinErrorText"></span>
                    </div>
                </div>

                <div class="space-y-2">
                    <button type="button" id="confirmPinBtn" class="w-full py-3.5 px-6 bg-[#008245] hover:bg-[#006b38] text-white font-bold text-xs rounded-xl shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                        <span id="pinLoader" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin hidden"></span>
                        <span id="confirmPinText">Authorize Now</span>
                    </button>
                    
                    <button type="button" id="btnBackToConfirm" class="w-full py-2.5 text-center text-xs font-semibold text-slate-400 hover:text-slate-600 transition-colors flex items-center justify-center gap-1.5">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        Back to Review
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap Modal API Shim -->
<script>
    (function() {
        // Define Bootstrap compatibility layer
        window.bootstrap = window.bootstrap || {};
        window.bootstrap.Modal = function(el) {
            this.el = el;
            this.show = function() {
                el.dispatchEvent(new CustomEvent('open-modal', { bubbles: true }));
                // Trigger Lucide to render icons inside the modal
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            };
            this.hide = function() {
                el.dispatchEvent(new CustomEvent('close-modal', { bubbles: true }));
            };
        };
        window.bootstrap.Modal.getOrCreateInstance = function(el) {
            return new window.bootstrap.Modal(el);
        };

        // Add back-to-confirm step toggle behavior directly here
        document.addEventListener('DOMContentLoaded', () => {
            const btnBack = document.getElementById('btnBackToConfirm');
            if (btnBack) {
                btnBack.addEventListener('click', () => {
                    const confStep = document.getElementById('confirmationStep');
                    const pinStep = document.getElementById('pinStep');
                    if (confStep) confStep.classList.remove('hidden');
                    if (pinStep) pinStep.classList.add('hidden');

                    const mt = document.getElementById('modalTitle');
                    const ms = document.getElementById('modalSubtitle');
                    if (mt) mt.textContent = 'Confirm Transaction';
                    if (ms) ms.textContent = 'Please review details carefully';
                });
            }
        });
    })();
</script>
