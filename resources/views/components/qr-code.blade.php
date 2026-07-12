@props(['data', 'size' => 200])

<div {{ $attributes->merge(['class' => 'inline-block rounded-lg border border-line bg-white p-3']) }}
     style="width: {{ $size + 24 }}px">
    {!! \App\Support\QrCode::svg($data, $size) !!}
</div>
