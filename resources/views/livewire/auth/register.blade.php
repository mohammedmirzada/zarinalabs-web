<div>
    <x-auth-card title="Create your account"
                 subtitle="One account for every ZARINALABS course, workshop and event."
                 width="max-w-2xl">
        <form wire:submit="register" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-form.label for="name">Full name</x-form.label>
                <x-form.input id="name" wire:model="name" autocomplete="name" />
                <x-form.error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-form.label for="gender">Gender</x-form.label>
                <x-form.select id="gender" wire:model="gender" :options="config('options.genders')" />
                <x-form.error :messages="$errors->get('gender')" />
            </div>

            <div>
                <x-form.label for="date_of_birth">Date of birth</x-form.label>
                <x-form.input id="date_of_birth" type="date" wire:model="date_of_birth" />
                <x-form.error :messages="$errors->get('date_of_birth')" />
            </div>

            <div>
                <x-form.label for="email">Email</x-form.label>
                <x-form.input id="email" type="email" wire:model="email" autocomplete="username" />
                <x-form.error :messages="$errors->get('email')" />
            </div>

            <div>
                <x-form.label for="phone">Phone</x-form.label>
                <x-form.input id="phone" wire:model="phone" placeholder="+964 750 000 0000" />
                <x-form.error :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-form.label for="password">Password</x-form.label>
                <x-form.input id="password" type="password" wire:model="password" autocomplete="new-password" />
                <x-form.error :messages="$errors->get('password')" />
            </div>

            <div>
                <x-form.label for="password_confirmation">Confirm password</x-form.label>
                <x-form.input id="password_confirmation" type="password" wire:model="password_confirmation"
                              autocomplete="new-password" />
                <x-form.error :messages="$errors->get('password_confirmation')" />
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

            <div class="sm:col-span-2">
                <x-form.label for="it_interest">IT interest field</x-form.label>
                <x-form.select id="it_interest" wire:model="it_interest" :options="config('options.it_interests')" />
                <x-form.error :messages="$errors->get('it_interest')" />
            </div>

            <div class="sm:col-span-2">
                <x-button class="w-full" wire:loading.attr="disabled">Create account</x-button>
            </div>
        </form>

        <x-slot:below>
            Already have an account?
            <a href="{{ route('login') }}" wire:navigate
               class="text-brand underline underline-offset-4 hover:text-brand-dark">Log in</a>
        </x-slot:below>
    </x-auth-card>
</div>
