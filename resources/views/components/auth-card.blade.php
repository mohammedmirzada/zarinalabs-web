@props(['title', 'subtitle' => null, 'width' => 'max-w-md'])

<div class="mx-auto w-full {{ $width }} px-4 py-16">
    <h1 class="text-2xl">{{ $title }}</h1>

    @if ($subtitle)
        <p class="mt-2 text-sm text-ink/70">{{ $subtitle }}</p>
    @endif

    <div class="mt-8 rounded-xl border border-line bg-white p-6">
        {{ $slot }}
    </div>

    @if (isset($below))
        <p class="mt-6 text-center text-sm text-ink/70">{{ $below }}</p>
    @endif
</div>
