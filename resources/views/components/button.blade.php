@props(['type' => 'submit'])

<button type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center rounded-lg bg-brand px-5 py-2.5 text-sm '
            . 'font-medium text-paper transition-colors hover:bg-brand-dark disabled:opacity-60',
    ]) }}>
    {{ $slot }}
</button>
