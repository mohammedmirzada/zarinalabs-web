<div class="mx-auto w-full max-w-2xl px-4 py-16">
    <h1 class="text-2xl">Your profile</h1>

    <div class="mt-8 rounded-xl border border-line bg-white p-6">
        <h2 class="text-base font-medium">Personal details</h2>

        <dl class="mt-5 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
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
        <p class="mt-4 text-sm text-ink/70">Email, gender and date of birth cannot be changed. Contact us if something is wrong.</p>
    </div>

    <div class="mt-6 rounded-xl border border-line bg-white p-6">
        <h2 class="text-base font-medium">Contact and interests</h2>

        @if (session('profile-status'))
            <div class="mt-4 rounded-lg border border-line bg-paper px-4 py-3 text-sm text-ink">
                {{ session('profile-status') }}
            </div>
        @endif

        <form wire:submit="updateProfile" class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-form.label for="name">Full name</x-form.label>
                <x-form.input id="name" wire:model="name" />
                <x-form.error :messages="$errors->get('name')" />
            </div>

            <div class="sm:col-span-2">
                <x-form.label for="avatar">Profile photo</x-form.label>
                <div class="mt-1 flex items-center gap-4">
                    {{-- Rounded square, never rounded-full (design tokens). --}}
                    <div class="size-16 shrink-0 overflow-hidden rounded-lg border border-line bg-paper">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" alt="New photo preview" class="size-full object-cover">
                        @elseif ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="Current photo" class="size-full object-cover">
                        @else
                            <div class="flex size-full items-center justify-center text-ink/40">
                                <x-heroicon-s-user class="size-8" />
                            </div>
                        @endif
                    </div>

                    <div class="text-sm">
                        <input type="file" id="avatar" wire:model="avatar" accept="image/*"
                               class="block w-full cursor-pointer text-sm text-ink/70
                                      file:mr-3 file:cursor-pointer file:rounded-lg file:border file:border-line
                                      file:bg-paper file:px-3 file:py-1.5 file:text-sm file:text-ink
                                      hover:file:bg-line/40">
                        <p class="mt-1 text-xs text-ink/60">JPG or PNG, up to 2 MB.</p>
                        @if ($user->avatarUrl() && ! $avatar)
                            <button type="button" wire:click="removeAvatar"
                                    class="mt-2 cursor-pointer text-xs text-brand transition-colors hover:text-brand-dark">
                                Remove photo
                            </button>
                        @endif
                    </div>
                </div>
                <div wire:loading wire:target="avatar" class="mt-1 text-xs text-ink/60">Uploading…</div>
                <x-form.error :messages="$errors->get('avatar')" />
            </div>

            <div>
                <x-form.label for="phone">Phone</x-form.label>
                <x-form.input id="phone" wire:model="phone" />
                <x-form.error :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-form.label for="city">City</x-form.label>
                <x-form.combobox id="city" model="city" :required="true" :live="false"
                                 :options="config('options.cities')" placeholder="Choose a city" />
                <x-form.error :messages="$errors->get('city')" />
            </div>

            <div>
                <x-form.label for="education_level">Highest education level</x-form.label>
                <x-form.combobox id="education_level" model="education_level" :required="true" :live="false"
                                 :options="config('options.education_levels')" placeholder="Choose a level" />
                <x-form.error :messages="$errors->get('education_level')" />
            </div>

            <div>
                <x-form.label for="it_interest">IT interest field</x-form.label>
                <x-form.combobox id="it_interest" model="it_interest" :required="true" :live="false"
                                 :options="config('options.it_interests')" placeholder="Choose a field" />
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
