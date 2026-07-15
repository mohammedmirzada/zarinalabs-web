<div>
    <section class="mx-auto max-w-3xl px-4 py-20">
        <h1 class="text-3xl leading-tight sm:text-4xl">Contact us</h1>

        <p class="mt-6 text-lg text-ink/70">
            Questions about a course, your registration, or anything else? Reach us directly —
            we read every message.
        </p>

        <div class="mt-12 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-line bg-white p-6">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-paper text-brand">
                        <x-heroicon-s-envelope class="size-5" />
                    </span>
                    <h2 class="text-base font-medium">Email</h2>
                </div>
                <a href="mailto:{{ config('contact.email') }}"
                   class="mt-4 block text-brand underline underline-offset-4 hover:text-brand-dark">
                    {{ config('contact.email') }}
                </a>
            </div>

            <div class="rounded-xl border border-line bg-white p-6">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-paper text-brand">
                        <x-heroicon-s-map-pin class="size-5" />
                    </span>
                    <h2 class="text-base font-medium">Where to find us</h2>
                </div>
                <p class="mt-4 text-ink/70">
                    <span class="font-medium text-ink">{{ config('contact.location.name') }}</span><br>
                    {{ config('contact.location.address') }}
                </p>
            </div>
        </div>

        <div class="mt-12">
            <a href="{{ route('courses.index') }}" wire:navigate
               class="inline-block rounded-lg bg-brand px-6 py-3 text-sm font-medium text-paper transition-colors hover:bg-brand-dark">
                Browse courses
            </a>
        </div>
    </section>
</div>
