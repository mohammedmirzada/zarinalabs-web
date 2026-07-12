<x-layouts.app title="Page not found">
    <div class="mx-auto max-w-2xl px-4 py-24 text-center">
        <h1 class="text-2xl">Page not found</h1>
        <p class="mt-4 text-ink/70">
            That page does not exist, or the course you are looking for is not published.
        </p>
        <a href="{{ route('courses.index') }}"
           class="mt-8 inline-block rounded-lg bg-brand px-6 py-3 text-sm font-medium text-paper transition-colors hover:bg-brand-dark">
            Browse courses
        </a>
    </div>
</x-layouts.app>
