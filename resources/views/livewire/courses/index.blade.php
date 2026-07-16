<div class="mx-auto max-w-6xl px-4 py-16">
    <h1 class="text-2xl">Courses and events</h1>
    <p class="mt-2 text-sm text-ink/70">Everything ZARINALABS has scheduled. Filter to find your fit.</p>

    <div class="mt-8 grid grid-cols-1 gap-4 rounded-xl border border-line bg-white p-5 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <x-form.label for="search">Search</x-form.label>
            <x-form.input id="search" wire:model.live.debounce.400ms="search" placeholder="Course title" />
        </div>

        <div>
            <x-form.label for="date">Starting on or after</x-form.label>
            <x-form.input id="date" type="date" wire:model.live="date" />
        </div>

        <div>
            <x-form.label for="category">Category</x-form.label>
            <x-form.combobox id="category" model="category" :options="config('options.categories')"
                             placeholder="All categories" />
        </div>

        <div>
            <x-form.label for="city">City</x-form.label>
            <x-form.combobox id="city" model="city" :options="config('options.cities')"
                             placeholder="All cities" />
        </div>

        @if ($this->hasFilters())
            <div class="flex items-end">
                <button type="button" wire:click="clearFilters"
                        class="text-sm text-brand underline underline-offset-4 hover:text-brand-dark">
                    Clear filters
                </button>
            </div>
        @endif
    </div>

    <div class="mt-4 text-sm text-ink/70" wire:loading.class="opacity-50">
        {{ $courses->total() }} {{ Str::plural('result', $courses->total()) }}
    </div>

    @if ($courses->isEmpty())
        <x-empty-state class="mt-6" title="No courses match those filters"
                       message="Try a different city or category, or clear the filters to see everything upcoming." />
    @else
        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $course)
                <x-course-card :course="$course" wire:key="course-{{ $course->id }}" />
            @endforeach
        </div>

        <div class="mt-10">
            {{ $courses->links() }}
        </div>
    @endif
</div>
