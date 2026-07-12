<?php

namespace App\Livewire;

use App\Models\Registration;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('My registrations')]
class MyRegistrations extends Component
{
    public function render()
    {
        $registrations = Registration::query()
            ->where('user_id', auth()->id())
            ->with(['course.location', 'course.sessions.location', 'attendances'])
            ->get()
            ->sortBy(fn (Registration $registration) => $registration->course->start_date);

        return view('livewire.my-registrations', ['registrations' => $registrations]);
    }
}
