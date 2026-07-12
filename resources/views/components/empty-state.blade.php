@props(['title', 'message' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-line bg-white px-6 py-16 text-center']) }}>
    <p class="font-medium text-ink">{{ $title }}</p>

    @if ($message)
        <p class="mt-2 text-sm text-ink/70">{{ $message }}</p>
    @endif

    {{ $slot }}
</div>
