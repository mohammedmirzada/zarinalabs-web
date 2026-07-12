{{-- The brand wordmark. Swap public/media/logo.svg to change it everywhere. --}}
<img src="{{ asset('media/logo.svg') }}" alt="ZARINALABS"
     {{ $attributes->merge(['class' => 'h-6 w-auto']) }}>
