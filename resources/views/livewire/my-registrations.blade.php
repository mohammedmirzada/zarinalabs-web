<div class="mx-auto max-w-4xl px-4 py-16">
    <h1 class="text-2xl">My registrations</h1>
    <p class="mt-2 text-sm text-ink/70">Show the QR code at the door. The admin scans it to mark you present.</p>

    @if ($registrations->isEmpty())
        <x-empty-state class="mt-8" title="You have not registered for anything yet"
                       message="Browse the upcoming courses and events and reserve a seat.">
            <a href="{{ route('courses.index') }}" wire:navigate
               class="mt-5 inline-block rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-paper transition-colors hover:bg-brand-dark">
                Browse courses
            </a>
        </x-empty-state>
    @else
        <div class="mt-8 space-y-6">
            @foreach ($registrations as $registration)
                @php
                    $course = $registration->course;
                    $attendanceBySession = $registration->attendances->keyBy('course_session_id');
                @endphp

                <article class="rounded-xl border border-line bg-white p-6" wire:key="reg-{{ $registration->id }}">
                    <div class="flex flex-col gap-6 sm:flex-row sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap gap-2">
                                <x-badge>{{ config('options.course_types')[$course->type] }}</x-badge>
                                <x-badge>{{ config('options.formats')[$course->format] }}</x-badge>
                            </div>

                            <h2 class="mt-3 text-lg font-semibold text-ink">
                                <a href="{{ route('courses.show', $course->slug) }}" wire:navigate class="hover:text-brand">
                                    {{ $course->title }}
                                </a>
                            </h2>

                            <p class="mt-1 text-sm text-ink/70">
                                {{ $course->start_date->format('j M Y') }} to {{ $course->end_date->format('j M Y') }}
                            </p>

                            @if ($course->format === 'online')
                                @if ($course->meeting_link)
                                    <p class="mt-3 text-sm text-ink/70">Meeting link</p>
                                    <a href="{{ $course->meeting_link }}" target="_blank" rel="noopener"
                                       class="block break-all text-sm text-brand underline underline-offset-4 hover:text-brand-dark">
                                        {{ $course->meeting_link }}
                                    </a>
                                @endif
                            @else
                                <p class="mt-3 text-sm text-ink/70">
                                    {{ $course->location?->name }} — {{ $course->location?->address }}
                                </p>
                            @endif
                        </div>

                        <div class="shrink-0">
                            <x-qr-code :data="$registration->checkInUrl()" :size="160" />
                        </div>
                    </div>

                    <div class="mt-6 border-t border-line pt-6">
                        <h3 class="text-sm font-medium">Attendance</h3>

                        <ul class="mt-3 divide-y divide-line rounded-lg border border-line">
                            @foreach ($course->sessions->sortBy('session_date') as $session)
                                <li class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                                    <span>{{ $session->session_date->format('D, j M Y') }}</span>
                                    <span class="text-ink/70">{{ $session->timeRange() }}</span>

                                    @if (! $session->isPast())
                                        <span class="text-ink/50">Upcoming</span>
                                    @elseif ($attendanceBySession->has($session->id))
                                        <span class="font-medium text-ink">Present</span>
                                    @else
                                        <span class="text-brand">Absent</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
