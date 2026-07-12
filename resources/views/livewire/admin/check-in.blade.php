<div class="mx-auto w-full max-w-md px-4 py-10">
    <h1 class="text-2xl">Check in</h1>

    <div class="mt-6 rounded-xl border border-line bg-white p-6">
        <p class="text-xs tracking-wide text-ink/70 uppercase">Student</p>
        <p class="mt-1 text-xl font-semibold">{{ $this->registration->user->name }}</p>
        <p class="text-sm text-ink/70">{{ $this->registration->user->email }}</p>

        <div class="mt-5 border-t border-line pt-5">
            <p class="text-xs tracking-wide text-ink/70 uppercase">Course</p>
            <p class="mt-1 font-medium">{{ $this->registration->course->title }}</p>
            <p class="text-sm text-ink/70">
                {{ $this->registration->course->format === 'online'
                    ? 'Online'
                    : $this->registration->course->location?->name }}
            </p>
        </div>
    </div>

    @if ($this->allSessions->isEmpty())
        <p class="mt-6 rounded-lg border border-line bg-paper px-4 py-3 text-sm text-ink">
            This course has no sessions yet, so there is nothing to check in to.
        </p>
    @else
        @if ($this->sessionsToday->isEmpty())
            <div class="mt-6 flex gap-3 rounded-lg border border-brand/30 bg-paper px-4 py-3 text-sm text-brand">
                <x-heroicon-s-exclamation-triangle class="size-5 shrink-0" />
                <p>No session is scheduled today. Pick the session you are checking this student into.</p>
            </div>
        @endif

        <div class="mt-6">
            <x-form.label for="session">Session</x-form.label>
            <select id="session" wire:model.live="selectedSessionId"
                    class="mt-1 block w-full rounded-lg border border-line bg-white px-3 py-2 text-ink focus:border-brand focus:ring-1 focus:ring-brand focus:outline-none">
                @foreach ($this->sessionsToday->isNotEmpty() ? $this->sessionsToday : $this->allSessions as $session)
                    <option value="{{ $session->id }}">
                        {{ $session->session_date->format('D, j M Y') }} — {{ $session->timeRange() }}
                    </option>
                @endforeach
            </select>
        </div>

        @if (session('checked-in'))
            <p class="mt-6 rounded-lg border border-line bg-paper px-4 py-3 text-sm font-medium text-ink">
                {{ session('checked-in') }}
            </p>
        @endif

        <div class="mt-6">
            @if ($this->attendance)
                <p class="rounded-lg border border-line bg-paper px-4 py-4 text-center font-medium text-ink">
                    Already checked in at {{ $this->attendance->checked_in_at->format('H:i') }}
                </p>
            @else
                <x-button class="w-full py-4 text-base" wire:click="markPresent" wire:loading.attr="disabled">
                    Mark present
                </x-button>
            @endif
        </div>
    @endif
</div>
