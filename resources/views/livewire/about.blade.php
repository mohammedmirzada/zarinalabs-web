<div>
    <section class="mx-auto max-w-3xl px-4 py-20">
        <h1 class="text-3xl leading-tight sm:text-4xl">About ZARINALABS</h1>

        <p class="mt-6 text-lg text-ink/70">
            ZARINALABS runs the IT courses, workshops and events for Lions Fort. We exist to
            build practical, job-ready technology skills across Iraq — taught in person, by
            people who do the work.
        </p>

        <div class="mt-12 space-y-10">
            <div>
                <h2 class="text-xl">What we do</h2>
                <p class="mt-3 text-ink/70">
                    We publish a schedule of courses and events, from single-session workshops to
                    multi-week programmes. You register online, receive a QR code, and are checked in
                    at the door. Everything is free to attend — there are no payments of any kind.
                </p>
            </div>

            <div>
                <h2 class="text-xl">How it works</h2>
                <ol class="mt-4 space-y-4 text-ink/70">
                    <li class="flex gap-3">
                        <x-heroicon-s-magnifying-glass class="mt-0.5 size-5 shrink-0 text-brand" />
                        <span><span class="font-medium text-ink">Browse.</span> Find a course or event that fits your level and interest.</span>
                    </li>
                    <li class="flex gap-3">
                        <x-heroicon-s-check-circle class="mt-0.5 size-5 shrink-0 text-brand" />
                        <span><span class="font-medium text-ink">Register.</span> Reserve your seat in a few clicks. We hold your place.</span>
                    </li>
                    <li class="flex gap-3">
                        <x-heroicon-s-qr-code class="mt-0.5 size-5 shrink-0 text-brand" />
                        <span><span class="font-medium text-ink">Attend.</span> Show your QR code at the door and get checked in.</span>
                    </li>
                </ol>
            </div>

            <div>
                <h2 class="text-xl">Who runs it</h2>
                <p class="mt-3 text-ink/70">
                    ZARINALABS is managed by
                    <a href="https://lionsfortco.com/" target="_blank" rel="noopener"
                       class="text-brand underline underline-offset-4 hover:text-brand-dark">Lions Fort</a>.
                </p>
            </div>
        </div>

        <div class="mt-12 flex flex-wrap gap-4">
            <a href="{{ route('courses.index') }}" wire:navigate
               class="inline-block rounded-lg bg-brand px-6 py-3 text-sm font-medium text-paper transition-colors hover:bg-brand-dark">
                Browse courses
            </a>
            <a href="{{ route('contact') }}" wire:navigate
               class="inline-block rounded-lg border border-line px-6 py-3 text-sm font-medium text-ink transition-colors hover:bg-line/40">
                Contact us
            </a>
        </div>
    </section>
</div>
