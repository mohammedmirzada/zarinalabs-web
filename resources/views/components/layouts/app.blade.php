{{-- Livewire pages pass $title through #[Title]; plain Blade pages pass it as a prop. --}}
@props(['title' => null])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ZARINALABS' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Michroma&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-screen flex-col">
    <nav class="sticky top-0 z-50 border-b border-line bg-paper" x-data="{ open: false }">
        <div class="mx-auto max-w-6xl px-4">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" wire:navigate aria-label="ZARINALABS home">
                    <x-logo />
                </a>

                {{-- lg, not md: the Michroma nav items plus the logo need ~750px. --}}
                <div class="hidden items-center gap-8 lg:flex">
                    <x-nav-link :href="route('courses.index')" :active="request()->routeIs('courses.*')">Courses</x-nav-link>

                    @auth
                        <x-nav-link :href="route('my-registrations')" :active="request()->routeIs('my-registrations')">My registrations</x-nav-link>
                        <x-nav-link :href="route('profile')" :active="request()->routeIs('profile')">Profile</x-nav-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="font-display text-sm text-ink transition-colors hover:text-brand">
                                Log out
                            </button>
                        </form>
                    @else
                        <x-nav-link :href="route('login')" :active="request()->routeIs('login')">Log in</x-nav-link>
                        <a href="{{ route('register') }}" wire:navigate
                           class="rounded-lg bg-brand px-4 py-2 font-display text-sm text-paper transition-colors hover:bg-brand-dark">
                            Register
                        </a>
                    @endauth
                </div>

                <button type="button" class="text-ink lg:hidden" @click="open = ! open"
                        :aria-expanded="open ? 'true' : 'false'" aria-label="Toggle menu">
                    <x-heroicon-s-bars-3 class="size-6" x-show="! open" />
                    <x-heroicon-s-x-mark class="size-6" x-show="open" x-cloak />
                </button>
            </div>

            <div class="flex flex-col gap-1 pb-4 lg:hidden" x-show="open" x-cloak>
                <x-nav-link :href="route('courses.index')" :active="request()->routeIs('courses.*')">Courses</x-nav-link>

                @auth
                    <x-nav-link :href="route('my-registrations')" :active="request()->routeIs('my-registrations')">My registrations</x-nav-link>
                    <x-nav-link :href="route('profile')" :active="request()->routeIs('profile')">Profile</x-nav-link>
                    <form method="POST" action="{{ route('logout') }}" class="py-2">
                        @csrf
                        <button type="submit" class="font-display text-sm text-ink transition-colors hover:text-brand">
                            Log out
                        </button>
                    </form>
                @else
                    <x-nav-link :href="route('login')" :active="request()->routeIs('login')">Log in</x-nav-link>
                    <x-nav-link :href="route('register')" :active="request()->routeIs('register')">Register</x-nav-link>
                @endauth
            </div>
        </div>
    </nav>

    @if (session('status'))
        <div class="mx-auto mt-6 w-full max-w-6xl px-4">
            <div class="rounded-lg border border-line bg-white px-4 py-3 text-sm text-ink">
                {{ session('status') }}
            </div>
        </div>
    @endif

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-line">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-8 text-sm text-ink/70 sm:flex-row">
            <p>&copy; {{ now()->year }} ZARINALABS</p>
            <p>
                Managed by
                <a href="https://lionsfortco.com/" target="_blank" rel="noopener"
                   class="text-brand underline underline-offset-4 hover:text-brand-dark">Lions Fort</a>
            </p>
        </div>
    </footer>

    {{-- Explicit: Livewire only auto-injects on pages that render a component, and the
         nav's Alpine toggle needs the bundled Alpine on static pages too. --}}
    @livewireScripts
</body>
</html>
