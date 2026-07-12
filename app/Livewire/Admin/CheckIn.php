<?php

namespace App\Livewire\Admin;

use App\Models\Attendance;
use App\Models\CourseSession;
use App\Models\Registration;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The QR code on a student's registration opens this page as a signed URL.
 * Reached only by a logged-in admin: see the signed + auth + admin middleware on the route.
 */
#[Layout('components.layouts.app')]
#[Title('Check in')]
class CheckIn extends Component
{
    #[Locked]
    public int $registrationId;

    public ?int $selectedSessionId = null;

    public function mount(string $registration): void
    {
        $found = Registration::where('uuid', $registration)->firstOrFail();

        $this->registrationId = $found->id;
        $this->selectedSessionId = $this->sessionsToday->first()?->id ?? $this->allSessions->first()?->id;
    }

    #[Computed]
    public function registration(): Registration
    {
        return Registration::with(['user', 'course.location'])->findOrFail($this->registrationId);
    }

    #[Computed]
    public function allSessions(): Collection
    {
        return $this->registration->course->sessions()->orderBy('session_date')->orderBy('start_time')->get();
    }

    #[Computed]
    public function sessionsToday(): Collection
    {
        return $this->allSessions->filter(fn (CourseSession $session) => $session->session_date->isToday())->values();
    }

    #[Computed]
    public function attendance(): ?Attendance
    {
        if (! $this->selectedSessionId) {
            return null;
        }

        return Attendance::where('registration_id', $this->registrationId)
            ->where('course_session_id', $this->selectedSessionId)
            ->first();
    }

    public function markPresent(): void
    {
        // The session must belong to this registration's course, whatever the browser sent.
        $session = CourseSession::where('course_id', $this->registration->course_id)
            ->findOrFail($this->selectedSessionId);

        Attendance::firstOrCreate(
            ['registration_id' => $this->registrationId, 'course_session_id' => $session->id],
            ['checked_in_at' => now()],
        );

        unset($this->attendance);

        session()->flash('checked-in', $this->registration->user->name.' is marked present.');
    }

    public function render()
    {
        return view('livewire.admin.check-in');
    }
}
