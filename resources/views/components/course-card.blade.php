@props(['course'])

<a href="{{ route('courses.show', $course->slug) }}" wire:navigate
   class="flex flex-col rounded-xl border border-line bg-white p-5 transition-colors hover:border-brand">
    <div class="flex flex-wrap gap-2">
        <x-badge>{{ config('options.course_types')[$course->type] }}</x-badge>
        <x-badge>{{ config('options.levels')[$course->level] }}</x-badge>
    </div>

    <h3 class="mt-4 text-base font-semibold text-ink">{{ $course->title }}</h3>

    <p class="mt-2 line-clamp-2 text-sm text-ink/70">{{ $course->description }}</p>

    <dl class="mt-4 space-y-1 text-sm text-ink/70">
        <div class="flex items-center gap-2">
            <x-heroicon-s-calendar-days class="size-4 shrink-0" />
            <dd>{{ $course->start_date->format('j M Y') }}</dd>
        </div>
        <div class="flex items-center gap-2">
            <x-heroicon-s-map-pin class="size-4 shrink-0" />
            <dd>{{ $course->format === 'online' ? 'Online' : $course->location?->name }}</dd>
        </div>
    </dl>

    <p class="mt-4 border-t border-line pt-4 text-sm">
        @if ($course->isFull())
            <span class="text-ink/70">No seats left</span>
        @else
            <span class="text-brand">{{ $course->seatsLeft() }} {{ Str::plural('seat', $course->seatsLeft()) }} left</span>
        @endif
    </p>
</a>
