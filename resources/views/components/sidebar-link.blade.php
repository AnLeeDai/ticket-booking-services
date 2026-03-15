@props([
    'href' => '#',
    'active' => false,
])

@php
    $base = 'flex items-center rounded-lg px-3 py-2 text-sm font-medium transition';
    $state = $active
        ? 'bg-[var(--brand)] text-white shadow-sm'
        : 'text-[var(--text-muted)] hover:bg-slate-100 hover:text-[var(--text)]';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $base . ' ' . $state]) }}>
    {{ $slot }}
</a>
