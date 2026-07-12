<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Verify your email')]
class VerifyEmail extends Component
{
    public function mount()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return $this->redirectRoute('home', navigate: true);
        }
    }

    public function resend(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectRoute('home', navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        session()->flash('status', 'A fresh verification link has been sent to your email address.');
    }

    public function render()
    {
        return view('livewire.auth.verify-email');
    }
}
