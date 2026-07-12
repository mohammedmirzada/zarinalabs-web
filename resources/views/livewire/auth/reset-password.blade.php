<div>
    <x-auth-card title="Reset your password" subtitle="Choose a new password for your account.">
        <form wire:submit="resetPassword" class="space-y-5">
            <div>
                <x-form.label for="email">Email</x-form.label>
                <x-form.input id="email" type="email" wire:model="email" autocomplete="username" />
                <x-form.error :messages="$errors->get('email')" />
            </div>

            <div>
                <x-form.label for="password">New password</x-form.label>
                <x-form.input id="password" type="password" wire:model="password" autocomplete="new-password" />
                <x-form.error :messages="$errors->get('password')" />
            </div>

            <div>
                <x-form.label for="password_confirmation">Confirm new password</x-form.label>
                <x-form.input id="password_confirmation" type="password" wire:model="password_confirmation"
                              autocomplete="new-password" />
                <x-form.error :messages="$errors->get('password_confirmation')" />
            </div>

            <x-button class="w-full" wire:loading.attr="disabled">Reset password</x-button>
        </form>
    </x-auth-card>
</div>
