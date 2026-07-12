<div>
    <section class="mx-auto max-w-6xl px-4 py-20">
        <h1 class="max-w-3xl text-3xl leading-tight sm:text-4xl">IT training, run properly.</h1>

        <p class="mt-6 max-w-2xl text-lg text-ink/70">
            ZARINALABS runs the courses, workshops and events that build practical IT skills in Iraq.
            Browse what is coming up, reserve your seat, and show your QR code at the door.
        </p>

        <div class="mt-10">
            <a href="{{ route('courses.index') }}" wire:navigate
               class="inline-block rounded-lg bg-brand px-6 py-3 text-sm font-medium text-paper transition-colors hover:bg-brand-dark">
                Browse courses
            </a>
        </div>
    </section>

    <section class="border-t border-line">
        <div class="mx-auto max-w-6xl px-4 py-16">
            <div class="flex flex-wrap items-baseline justify-between gap-4">
                <h2 class="text-xl">Coming up</h2>
                <a href="{{ route('courses.index') }}" wire:navigate
                   class="text-sm text-brand underline underline-offset-4 hover:text-brand-dark">
                    Browse all courses
                </a>
            </div>

            @if ($courses->isEmpty())
                <x-empty-state class="mt-8" title="Nothing scheduled yet"
                               message="New courses and events are announced regularly. Check back soon." />
            @else
                <div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($courses as $course)
                        <x-course-card :course="$course" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
