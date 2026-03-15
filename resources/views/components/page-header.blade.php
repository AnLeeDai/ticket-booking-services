@props([
    'title' => '',
    'subtitle' => '',
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-wrap items-start justify-between gap-4']) }}>
    <div>
        <h1 class="text-2xl font-semibold">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-[var(--text-muted)]">{{ $subtitle }}</p>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
