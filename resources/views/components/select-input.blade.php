@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400']) }}>
    {{ $slot }}
</select>
