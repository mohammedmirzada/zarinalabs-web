@props(['messages' => []])

@if ($messages)
    <ul class="mt-1 space-y-1 text-sm text-brand">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
