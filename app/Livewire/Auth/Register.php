<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Create your account')]
class Register extends Component
{
    public string $name = '';

    public string $gender = '';

    public string $date_of_birth = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $city = '';

    public string $education_level = '';

    public string $it_interest = '';

    public function register()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(array_keys(config('options.genders')))],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'city' => ['required', Rule::in(array_keys(config('options.cities')))],
            'education_level' => ['required', Rule::in(array_keys(config('options.education_levels')))],
            'it_interest' => ['required', Rule::in(array_keys(config('options.it_interests')))],
        ]);

        $user = User::create($validated);

        event(new Registered($user));

        Auth::login($user);

        return $this->redirectRoute('verification.notice', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
