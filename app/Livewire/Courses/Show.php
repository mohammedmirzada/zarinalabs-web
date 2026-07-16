<?php

namespace App\Livewire\Courses;

use App\Actions\RegisterUserForCourse;
use App\Exceptions\RegistrationNotAllowed;
use App\Models\Course;
use App\Models\Registration;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        // 404 rather than leak the existence of a draft.
        abort_unless(Course::published()->where('slug', $slug)->exists(), 404);
    }

    #[Computed]
    public function course(): Course
    {
        return Course::published()
            ->with(['instructor', 'sessions'])
            ->where('slug', $this->slug)
            ->firstOrFail();
    }

    #[Computed]
    public function registration(): ?Registration
    {
        if (! auth()->check()) {
            return null;
        }

        return Registration::where('user_id', auth()->id())
            ->where('course_id', $this->course->id)
            ->first();
    }

    public function register()
    {
        if (! auth()->check()) {
            return $this->redirectRoute('login', navigate: true);
        }

        try {
            app(RegisterUserForCourse::class)->execute(auth()->user(), $this->course);
        } catch (RegistrationNotAllowed $e) {
            $this->addError('registration', $e->getMessage());
            unset($this->course, $this->registration);

            return null;
        }

        session()->flash('status', 'You are registered. Your QR code is on the My Registrations page.');

        return $this->redirectRoute('my-registrations', navigate: true);
    }

    public function render()
    {
        return view('livewire.courses.show')->title($this->course->title);
    }
}
