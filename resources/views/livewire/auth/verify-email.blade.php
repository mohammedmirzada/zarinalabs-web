<div>
    <x-auth-card title="Verify your email"
                 subtitle="We sent a verification link to {{ auth()->user()->email }}. Open it to finish setting up your account.">
        @if (session('status'))
            <div class="mb-5 rounded-lg border border-line bg-paper px-4 py-3 text-sm text-ink">
                {{ session('status') }}
            </div>
        @endif

        <p class="text-sm text-ink/70">
            You can browse courses without verifying, but you cannot register for one until your email is confirmed.
            If the link never arrived, check your spam folder or send a new one.
        </p>

        <div class="mt-6 flex flex-wrap items-center gap-4">
            <x-button type="button" wire:click="resend" wire:loading.attr="disabled">
                Resend verification email
            </x-button>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-ink/70 underline underline-offset-4 hover:text-brand">
                    Log out
                </button>
            </form>
        </div>
    </x-auth-card>
</div>
