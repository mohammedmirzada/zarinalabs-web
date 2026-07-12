<div>
    <x-auth-card title="Log in" subtitle="Welcome back.">
        <form wire:submit="login" class="space-y-5">
            <div>
                <x-form.label for="email">Email</x-form.label>
                <x-form.input id="email" type="email" wire:model="email" autocomplete="username" />
                <x-form.error :messages="$errors->get('email')" />
            </div>

            <div>
                <x-form.label for="password">Password</x-form.label>
                <x-form.input id="password" type="password" wire:model="password" autocomplete="current-password" />
                <x-form.error :messages="$errors->get('password')" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-ink/70">
                    <input type="checkbox" wire:model="remember"
                           class="rounded border-line text-brand focus:ring-brand">
                    Remember me
                </label>

                <a href="{{ route('password.request') }}" wire:navigate
                   class="text-sm text-brand underline underline-offset-4 hover:text-brand-dark">
                    Forgot password?
                </a>
            </div>

            <x-button class="w-full" wire:loading.attr="disabled">Log in</x-button>
        </form>

        <x-slot:below>
            Need an account?
            <a href="{{ route('register') }}" wire:navigate
               class="text-brand underline underline-offset-4 hover:text-brand-dark">Create one</a>
        </x-slot:below>
    </x-auth-card>
</div>
