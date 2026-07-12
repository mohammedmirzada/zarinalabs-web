@props(['active' => false])

<a {{ $attributes->merge([
        'class' => 'font-display text-sm py-2 transition-colors '
            . ($active ? 'text-brand' : 'text-ink hover:text-brand'),
   ]) }}>
    {{ $slot }}
</a>
