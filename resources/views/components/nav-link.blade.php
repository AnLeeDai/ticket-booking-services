@props([
    'href' => '#',
    'active' => false,
])

@php
    $base = 'inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition';
    $state = $active
        ? 'bg-[var(--brand-soft)] text-[var(--brand)]'
        : 'text-[var(--text-muted)] hover:bg-slate-100 hover:text-[var(--text)]';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $base . ' ' . $state]) }}>
    {{ $slot }}
</a>
