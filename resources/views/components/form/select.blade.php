@props(['options' => [], 'placeholder' => 'Choose one'])

<select
    {{ $attributes->merge([
        'class' => 'mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2 text-ink '
            . 'focus:border-brand focus:ring-1 focus:ring-brand focus:outline-none',
    ]) }}>
    <option value="">{{ $placeholder }}</option>
    @foreach ($options as $key => $label)
        <option value="{{ $key }}">{{ $label }}</option>
    @endforeach
</select>
