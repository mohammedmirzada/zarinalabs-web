@props(['type' => 'text'])

<input type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2 text-ink '
            . 'placeholder:text-ink/40 focus:border-brand focus:ring-1 focus:ring-brand focus:outline-none',
    ]) }}>
