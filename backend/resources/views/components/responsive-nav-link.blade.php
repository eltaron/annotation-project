@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-r-4 border-cyan-400 text-start text-base font-medium text-cyan-300 bg-cyan-500/10 focus:outline-none focus:text-cyan-200 focus:bg-cyan-500/20 focus:border-cyan-300 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-r-4 border-transparent text-start text-base font-medium text-slate-400 hover:text-white hover:bg-white/10 hover:border-white/20 focus:outline-none focus:text-white focus:bg-white/10 focus:border-white/20 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
