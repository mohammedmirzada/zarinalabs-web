<div>
    <x-auth-card title="Forgot your password"
                 subtitle="Give us your email and we will send you a reset link.">
        @if (session('status'))
            <div class="mb-5 rounded-lg border border-line bg-paper px-4 py-3 text-sm text-ink">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit="sendResetLink" class="space-y-5">
            <div>
                <x-form.label for="email">Email</x-form.label>
                <x-form.input id="email" type="email" wire:model="email" autocomplete="username" />
                <x-form.error :messages="$errors->get('email')" />
            </div>

            <x-button class="w-full" wire:loading.attr="disabled">Send reset link</x-button>
        </form>

        <x-slot:below>
            <a href="{{ route('login') }}" wire:navigate
               class="text-brand underline underline-offset-4 hover:text-brand-dark">Back to log in</a>
        </x-slot:below>
    </x-auth-card>
</div>
