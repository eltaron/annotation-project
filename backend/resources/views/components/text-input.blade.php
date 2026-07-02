@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-700 bg-slate-800 text-white focus:border-cyan-500 focus:ring-cyan-500 rounded-xl shadow-sm text-sm']) }}>
