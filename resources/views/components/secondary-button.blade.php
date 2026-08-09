<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white border border-slate-200 font-semibold text-sm text-slate-700 shadow-sm transition-all hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
