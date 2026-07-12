<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use App\Models\Attendance as AttendanceModel;
use App\Models\CourseSession;
use App\Models\Registration;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

/**
 * Registered students (rows) by sessions (columns). The admin reads this matrix and
 * decides who completed the course. Nothing here is automated.
 */
class Attendance extends Page
{
    use InteractsWithRecord;

    protected static string $resource = CourseResource::class;

    protected string $view = 'filament.resources.courses.pages.attendance';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'Attendance: '.$this->record->title;
    }

    #[Computed]
    public function sessions(): Collection
    {
        return $this->record->sessions()->orderBy('session_date')->orderBy('start_time')->get();
    }

    #[Computed]
    public function registrations(): Collection
    {
        return $this->record->registrations()
            ->with('user')
            ->get()
            ->sortBy(fn (Registration $registration) => $registration->user->name)
            ->values();
    }

    /** Set of "registrationId:sessionId" for every present mark, fetched in one query. */
    #[Computed]
    public function present(): Collection
    {
        return AttendanceModel::whereIn('registration_id', $this->registrations->pluck('id'))
            ->get()
            ->keyBy(fn (AttendanceModel $attendance) => $attendance->registration_id.':'.$attendance->course_session_id);
    }

    public function isPresent(int $registrationId, int $sessionId): bool
    {
        return $this->present->has($registrationId.':'.$sessionId);
    }

    public function toggle(int $registrationId, int $sessionId): void
    {
        // Both must belong to this course, whatever the browser sent.
        $registration = Registration::where('course_id', $this->record->id)->findOrFail($registrationId);
        $session = CourseSession::where('course_id', $this->record->id)->findOrFail($sessionId);

        $attendance = AttendanceModel::where('registration_id', $registration->id)
            ->where('course_session_id', $session->id)
            ->first();

        if ($attendance) {
            $attendance->delete();
        } else {
            AttendanceModel::create([
                'registration_id' => $registration->id,
                'course_session_id' => $session->id,
                'checked_in_at' => now(),
            ]);
        }

        unset($this->present);
    }
}
