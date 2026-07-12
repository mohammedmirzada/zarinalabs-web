@props(['for' => null])

<label for="{{ $for }}" {{ $attributes->merge(['class' => 'block text-sm font-medium text-ink']) }}>
    {{ $slot }}
</label>
