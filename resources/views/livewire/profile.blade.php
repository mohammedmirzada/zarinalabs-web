<div class="mx-auto w-full max-w-2xl px-4 py-16">
    <h1 class="text-2xl">Your profile</h1>

    <div class="mt-8 rounded-xl border border-line bg-white p-6">
        <h2 class="text-base font-medium">Personal details</h2>
        <p class="mt-1 text-sm text-ink/70">These cannot be changed. Contact us if something is wrong.</p>

        <dl class="mt-5 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-ink/70">Full name</dt>
                <dd class="mt-1">{{ $user->name }}</dd>
            </div>
            <div>
                <dt class="text-ink/70">Email</dt>
                <dd class="mt-1">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="text-ink/70">Gender</dt>
                <dd class="mt-1">{{ config('options.genders')[$user->gender] }}</dd>
            </div>
            <div>
                <dt class="text-ink/70">Date of birth</dt>
                <dd class="mt-1">{{ $user->date_of_birth->format('j F Y') }}</dd>
            </div>
        </dl>
    </div>

    <div class="mt-6 rounded-xl border border-line bg-white p-6">
        <h2 class="text-base font-medium">Contact and interests</h2>

        @if (session('profile-status'))
            <div class="mt-4 rounded-lg border border-line bg-paper px-4 py-3 text-sm text-ink">
                {{ session('profile-status') }}
            </div>
        @endif

        <form wire:submit="updateProfile" class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-form.label for="phone">Phone</x-form.label>
                <x-form.input id="phone" wire:model="phone" />
                <x-form.error :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-form.label for="city">City</x-form.label>
                <x-form.select id="city" wire:model="city" :options="config('options.cities')" />
                <x-form.error :messages="$errors->get('city')" />
            </div>

            <div>
                <x-form.label for="education_level">Highest education level</x-form.label>
                <x-form.select id="education_level" wire:model="education_level"
                               :options="config('options.education_levels')" />
                <x-form.error :messages="$errors->get('education_level')" />
            </div>

            <div>
                <x-form.label for="it_interest">IT interest field</x-form.label>
                <x-form.select id="it_interest" wire:model="it_interest" :options="config('options.it_interests')" />
                <x-form.error :messages="$errors->get('it_interest')" />
            </div>

            <div class="sm:col-span-2">
                <x-button wire:loading.attr="disabled">Save changes</x-button>
            </div>
        </form>
    </div>

    <div class="mt-6 rounded-xl border border-line bg-white p-6">
        <h2 class="text-base font-medium">Change password</h2>

        @if (session('password-status'))
            <div class="mt-4 rounded-lg border border-line bg-paper px-4 py-3 text-sm text-ink">
                {{ session('password-status') }}
            </div>
        @endif

        <form wire:submit="updatePassword" class="mt-5 space-y-5">
            <div>
                <x-form.label for="current_password">Current password</x-form.label>
                <x-form.input id="current_password" type="password" wire:model="current_password"
                              autocomplete="current-password" />
                <x-form.error :messages="$errors->get('current_password')" />
            </div>

            <div>
                <x-form.label for="new_password">New password</x-form.label>
                <x-form.input id="new_password" type="password" wire:model="password" autocomplete="new-password" />
                <x-form.error :messages="$errors->get('password')" />
            </div>

            <div>
                <x-form.label for="new_password_confirmation">Confirm new password</x-form.label>
                <x-form.input id="new_password_confirmation" type="password" wire:model="password_confirmation"
                              autocomplete="new-password" />
                <x-form.error :messages="$errors->get('password_confirmation')" />
            </div>

            <x-button wire:loading.attr="disabled">Change password</x-button>
        </form>
    </div>
</div>
