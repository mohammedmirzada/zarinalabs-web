<div class="mx-auto max-w-6xl px-4 py-16">
    <a href="{{ route('courses.index') }}" wire:navigate
       class="text-sm text-brand underline underline-offset-4 hover:text-brand-dark">
        Back to courses
    </a>

    <div class="mt-8 grid grid-cols-1 gap-10 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="flex flex-wrap gap-2">
                <x-badge>{{ config('options.course_types')[$this->course->type] }}</x-badge>
                <x-badge>{{ config('options.categories')[$this->course->category] }}</x-badge>
            </div>

            {{-- Titles are admin-entered, so a single long word must not push the page sideways. --}}
            <h1 class="mt-4 text-2xl leading-tight break-words sm:text-3xl">{{ $this->course->title }}</h1>

            @if ($this->course->embedUrl())
                <div class="mt-8 aspect-video overflow-hidden rounded-xl border border-line">
                    <iframe src="{{ $this->course->embedUrl() }}" title="{{ $this->course->title }}"
                            class="h-full w-full" allowfullscreen
                            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>
            @endif

            <div class="mt-8 space-y-4 whitespace-pre-line text-ink/80">{{ $this->course->description }}</div>

            @if ($this->course->instructor)
                <div class="mt-10">
                    <h2 class="text-lg">Instructor</h2>

                    <div class="mt-4 flex gap-5 rounded-xl border border-line bg-white p-5">
                        @if ($this->course->instructor->photo_path)
                            <img src="{{ asset('storage/'.$this->course->instructor->photo_path) }}"
                                 alt="{{ $this->course->instructor->name }}"
                                 class="size-20 shrink-0 rounded-lg object-cover">
                        @endif

                        <div>
                            <p class="font-semibold text-ink">{{ $this->course->instructor->name }}</p>
                            <p class="mt-1 text-sm text-ink/70">{{ $this->course->instructor->bio }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-10">
                <h2 class="text-lg">Sessions</h2>

                @if ($this->course->sessions->isEmpty())
                    <p class="mt-4 text-sm text-ink/70">No sessions have been scheduled yet.</p>
                @else
                    <ul class="mt-4 divide-y divide-line rounded-xl border border-line bg-white">
                        @foreach ($this->course->sessions->sortBy('session_date') as $session)
                            <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 text-sm">
                                <span class="font-medium">{{ $session->session_date->format('D, j M Y') }}</span>
                                <span class="text-ink/70">{{ $session->timeRange() }}</span>
                                <span class="text-ink/70">
                                    {{ $this->course->format === 'online' ? 'Online' : $this->course->location }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <aside class="lg:col-span-1">
            <div class="rounded-xl border border-line bg-white p-6 lg:sticky lg:top-24">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-ink/70">Dates</dt>
                        <dd class="mt-1 font-medium">
                            {{ $this->course->start_date->format('j M Y') }} to {{ $this->course->end_date->format('j M Y') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-ink/70">Sessions</dt>
                        <dd class="mt-1 font-medium">{{ $this->course->sessions->count() }}</dd>
                    </div>

                    <div>
                        <dt class="text-ink/70">Format</dt>
                        <dd class="mt-1 font-medium">{{ config('options.formats')[$this->course->format] }}</dd>
                    </div>

                    <div>
                        <dt class="text-ink/70">{{ $this->course->format === 'online' ? 'Where' : 'Location' }}</dt>
                        <dd class="mt-1 font-medium">
                            @if ($this->course->format === 'online')
                                Online
                            @else
                                {{ $this->course->location }}
                                <span class="block font-normal text-ink/70">
                                    {{ config('options.cities')[$this->course->city] ?? $this->course->city }}
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-ink/70">Registration</dt>
                        <dd class="mt-1 font-medium">{{ $this->course->isOpen() ? 'Open' : 'Closed' }}</dd>
                    </div>

                    <div>
                        <dt class="text-ink/70">Registration deadline</dt>
                        <dd class="mt-1 font-medium">{{ $this->course->registration_deadline->format('j M Y') }}</dd>
                    </div>
                </dl>

                @error('registration')
                    <p class="mt-5 rounded-lg border border-brand/30 bg-paper px-4 py-3 text-sm text-brand">{{ $message }}</p>
                @enderror

                <div class="mt-6">
                    @if ($this->registration)
                        <p class="rounded-lg border border-line bg-paper px-4 py-3 text-center text-sm font-medium text-ink">
                            You are registered
                        </p>
                        <a href="{{ route('my-registrations') }}" wire:navigate
                           class="mt-3 block text-center text-sm text-brand underline underline-offset-4 hover:text-brand-dark">
                            View my registrations
                        </a>
                    @elseif (! auth()->check())
                        <a href="{{ route('login') }}" wire:navigate
                           class="block w-full rounded-lg bg-brand px-5 py-2.5 text-center text-sm font-medium text-paper transition-colors hover:bg-brand-dark">
                            Log in to register
                        </a>
                    @elseif (! auth()->user()->hasVerifiedEmail())
                        <a href="{{ route('verification.notice') }}" wire:navigate
                           class="block w-full rounded-lg bg-brand px-5 py-2.5 text-center text-sm font-medium text-paper transition-colors hover:bg-brand-dark">
                            Verify your email
                        </a>
                    @elseif ($this->course->deadlinePassed())
                        <button type="button" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-line px-5 py-2.5 text-sm font-medium text-ink/50">
                            Deadline passed
                        </button>
                    @elseif (! $this->course->is_accepting)
                        <button type="button" disabled
                                class="w-full cursor-not-allowed rounded-lg border border-line px-5 py-2.5 text-sm font-medium text-ink/50">
                            Registration closed
                        </button>
                    @else
                        <x-button class="w-full" wire:click="register" wire:loading.attr="disabled">
                            Register
                        </x-button>
                    @endif
                </div>

                {{-- The meeting link is never rendered for anyone who is not registered. --}}
                @if ($this->registration && $this->course->format === 'online' && $this->course->meeting_link)
                    <div class="mt-6 border-t border-line pt-6">
                        <p class="text-sm text-ink/70">Meeting link</p>
                        <a href="{{ $this->course->meeting_link }}" target="_blank" rel="noopener"
                           class="mt-1 block break-all text-sm text-brand underline underline-offset-4 hover:text-brand-dark">
                            {{ $this->course->meeting_link }}
                        </a>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
