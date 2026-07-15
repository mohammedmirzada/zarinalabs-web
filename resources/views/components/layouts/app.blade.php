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
    <nav class="sticky top-0 z-50 border-b border-line bg-paper" x-data="{ open: false, logoutOpen: false, userMenu: false }">
        <div class="mx-auto max-w-6xl px-4">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" wire:navigate aria-label="ZARINALABS home">
                    <x-logo />
                </a>

                {{-- lg, not md: the Michroma nav items plus the logo need ~750px. --}}
                <div class="hidden items-center gap-8 lg:flex">
                    <x-nav-link :href="route('courses.index')" :active="request()->routeIs('courses.*')">Courses</x-nav-link>
                    <x-nav-link :href="route('about')" :active="request()->routeIs('about')">About</x-nav-link>
                    <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-nav-link>

                    @auth
                        {{-- Avatar opens a menu on hover (click/keyboard also work). --}}
                        <div class="relative" @mouseenter="userMenu = true" @mouseleave="userMenu = false">
                            <button type="button" @click="userMenu = ! userMenu"
                                    @keydown.escape="userMenu = false"
                                    :aria-expanded="userMenu"
                                    aria-haspopup="menu"
                                    aria-label="Account menu"
                                    class="flex cursor-pointer items-center gap-2 rounded-lg p-1 transition-colors hover:bg-line/40 focus:outline-none focus:ring-1 focus:ring-brand">
                                @php $avatarUrl = auth()->user()->avatarUrl(); @endphp
                                {{-- Rounded square, never rounded-full (design tokens). --}}
                                <span class="block size-8 shrink-0 overflow-hidden rounded-lg border border-line bg-paper">
                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="" class="size-full object-cover">
                                    @else
                                        <span class="flex size-full items-center justify-center text-ink/40">
                                            <x-heroicon-s-user class="size-5" />
                                        </span>
                                    @endif
                                </span>
                                <x-heroicon-s-chevron-down class="size-4 text-ink/50 transition-transform"
                                                           ::class="userMenu && 'rotate-180'" />
                            </button>

                            <div x-show="userMenu" x-cloak
                                 x-transition.origin.top.duration.120ms
                                 role="menu"
                                 class="absolute right-0 mt-1 w-52 rounded-lg border border-line bg-white py-1 shadow-lg">
                                <div class="border-b border-line px-4 py-2">
                                    <p class="truncate text-sm font-medium text-ink">{{ auth()->user()->name }}</p>
                                    <p class="truncate text-xs text-ink/60">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('profile') }}" wire:navigate role="menuitem"
                                   class="block px-4 py-2 text-sm text-ink transition-colors hover:bg-paper">Profile</a>
                                <a href="{{ route('my-registrations') }}" wire:navigate role="menuitem"
                                   class="block px-4 py-2 text-sm text-ink transition-colors hover:bg-paper">My registrations</a>
                                <button type="button" @click="userMenu = false; logoutOpen = true" role="menuitem"
                                        class="block w-full cursor-pointer px-4 py-2 text-left text-sm text-brand transition-colors hover:bg-paper hover:text-brand-dark">
                                    Log out
                                </button>
                            </div>
                        </div>
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
                <x-nav-link :href="route('about')" :active="request()->routeIs('about')">About</x-nav-link>
                <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-nav-link>

                @auth
                    <div class="mt-1 flex items-center gap-3 border-b border-line pb-3">
                        @php $avatarUrl = auth()->user()->avatarUrl(); @endphp
                        <span class="block size-9 shrink-0 overflow-hidden rounded-lg border border-line bg-paper">
                            @if ($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="" class="size-full object-cover">
                            @else
                                <span class="flex size-full items-center justify-center text-ink/40">
                                    <x-heroicon-s-user class="size-5" />
                                </span>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-ink/60">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <x-nav-link :href="route('profile')" :active="request()->routeIs('profile')">Profile</x-nav-link>
                    <x-nav-link :href="route('my-registrations')" :active="request()->routeIs('my-registrations')">My registrations</x-nav-link>
                    <button type="button" @click="open = false; logoutOpen = true"
                            class="cursor-pointer py-2 text-left font-display text-sm text-brand transition-colors hover:text-brand-dark">
                        Log out
                    </button>
                @else
                    <x-nav-link :href="route('login')" :active="request()->routeIs('login')">Log in</x-nav-link>
                    <x-nav-link :href="route('register')" :active="request()->routeIs('register')">Register</x-nav-link>
                @endauth
            </div>
        </div>

        @auth
            {{-- Log out confirmation dialog --}}
            <div x-show="logoutOpen" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-ink/40 px-4"
                 @keydown.escape.window="logoutOpen = false">
                <div class="w-full max-w-sm rounded-xl border border-line bg-paper p-6 shadow-lg"
                     @click.outside="logoutOpen = false">
                    <h2 class="font-display text-lg text-ink">Log out</h2>
                    <p class="mt-2 text-sm text-ink/70">Are you sure you want to log out of your account?</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="logoutOpen = false"
                                class="cursor-pointer rounded-lg border border-line px-4 py-2 text-sm text-ink transition-colors hover:bg-line/40">
                            Cancel
                        </button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="cursor-pointer rounded-lg bg-brand px-4 py-2 text-sm text-paper transition-colors hover:bg-brand-dark">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endauth
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

    <footer class="border-t border-line bg-white">
        <div class="mx-auto max-w-6xl px-4 py-12">
            <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <x-logo />
                    <p class="mt-4 max-w-xs text-sm text-ink/70">
                        Practical IT training for Lions Fort — courses and events across Iraq,
                        free to attend.
                    </p>
                </div>

                <div>
                    <h3 class="font-display text-sm text-ink">Explore</h3>
                    <ul class="mt-4 space-y-2 text-sm text-ink/70">
                        <li><a href="{{ route('courses.index') }}" wire:navigate class="transition-colors hover:text-brand">Courses</a></li>
                        <li><a href="{{ route('about') }}" wire:navigate class="transition-colors hover:text-brand">About</a></li>
                        <li><a href="{{ route('contact') }}" wire:navigate class="transition-colors hover:text-brand">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-display text-sm text-ink">Account</h3>
                    <ul class="mt-4 space-y-2 text-sm text-ink/70">
                        @auth
                            <li><a href="{{ route('my-registrations') }}" wire:navigate class="transition-colors hover:text-brand">My registrations</a></li>
                            <li><a href="{{ route('profile') }}" wire:navigate class="transition-colors hover:text-brand">Profile</a></li>
                        @else
                            <li><a href="{{ route('login') }}" wire:navigate class="transition-colors hover:text-brand">Log in</a></li>
                            <li><a href="{{ route('register') }}" wire:navigate class="transition-colors hover:text-brand">Register</a></li>
                        @endauth
                    </ul>
                </div>

                <div>
                    <h3 class="font-display text-sm text-ink">Get in touch</h3>
                    <ul class="mt-4 space-y-3 text-sm text-ink/70">
                        <li class="flex items-start gap-2">
                            <x-heroicon-s-envelope class="mt-0.5 size-4 shrink-0 text-ink/50" />
                            <a href="mailto:{{ config('contact.email') }}" class="transition-colors hover:text-brand">{{ config('contact.email') }}</a>
                        </li>
                        <li class="flex items-start gap-2">
                            <x-heroicon-s-map-pin class="mt-0.5 size-4 shrink-0 text-ink/50" />
                            <span>{{ config('contact.location.name') }}, {{ config('contact.location.address') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-2 border-t border-line pt-6 text-sm text-ink/70 sm:flex-row">
                <p>&copy; {{ now()->year }} ZARINALABS</p>
                <p>
                    Managed by
                    <a href="https://lionsfortco.com/" target="_blank" rel="noopener"
                       class="text-brand underline underline-offset-4 hover:text-brand-dark">Lions Fort</a>
                </p>
            </div>
        </div>
    </footer>

    {{-- Explicit: Livewire only auto-injects on pages that render a component, and the
         nav's Alpine toggle needs the bundled Alpine on static pages too. --}}
    @livewireScripts
</body>
</html>
