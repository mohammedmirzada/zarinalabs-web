<x-layouts.app title="Not allowed">
    <div class="mx-auto max-w-2xl px-4 py-24 text-center">
        <h1 class="text-2xl">Not allowed</h1>
        <p class="mt-4 text-ink/70">
            You do not have access to this page. If you scanned a QR code, the link may have expired
            or you may not be signed in as an admin.
        </p>
        <a href="{{ route('home') }}"
           class="mt-8 inline-block rounded-lg bg-brand px-6 py-3 text-sm font-medium text-paper transition-colors hover:bg-brand-dark">
            Back to the home page
        </a>
    </div>
</x-layouts.app>
