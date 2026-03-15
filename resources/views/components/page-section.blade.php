@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'panel p-5']) }}>
    @if ($title || $description)
        <header class="mb-4 border-b border-[var(--line)] pb-3">
            @if ($title)
                <h2 class="text-lg font-semibold">{{ $title }}</h2>
            @endif

            @if ($description)
                <p class="mt-1 text-sm text-[var(--text-muted)]">{{ $description }}</p>
            @endif
        </header>
    @endif

    {{ $slot }}
</section>
