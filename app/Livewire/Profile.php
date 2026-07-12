<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

// Name, gender, date of birth and email are read-only in v1.
#[Layout('components.layouts.app')]
#[Title('Your profile')]
class Profile extends Component
{
    public string $phone = '';

    public string $city = '';

    public string $education_level = '';

    public string $it_interest = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->phone = $user->phone;
        $this->city = $user->city;
        $this->education_level = $user->education_level;
        $this->it_interest = $user->it_interest;
    }

    public function updateProfile(): void
    {
        $validated = $this->validate([
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', Rule::in(array_keys(config('options.cities')))],
            'education_level' => ['required', Rule::in(array_keys(config('options.education_levels')))],
            'it_interest' => ['required', Rule::in(array_keys(config('options.it_interests')))],
        ]);

        Auth::user()->update($validated);

        session()->flash('profile-status', 'Your details have been saved.');
    }

    public function updatePassword(): void
    {
        $validated = $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        Auth::user()->update(['password' => $validated['password']]);

        $this->reset('current_password', 'password', 'password_confirmation');

        session()->flash('password-status', 'Your password has been changed.');
    }

    public function render()
    {
        return view('livewire.profile', ['user' => Auth::user()]);
    }
}
