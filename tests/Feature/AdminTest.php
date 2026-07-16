<?php

namespace Tests\Feature;

use App\Filament\Resources\Courses\Pages\Attendance as AttendancePage;
use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Users\UserResource;
use App\Livewire\Admin\CheckIn;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Instructor;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function course(): Course
    {
        return Course::factory()->create([
            'is_published' => true,
            'start_date' => today()->subDays(3),
            'end_date' => today()->addDays(3),
            'registration_deadline' => today()->subDays(5),
        ]);
    }

    // ---- panel access -------------------------------------------------------

    public function test_only_admins_can_access_the_panel(): void
    {
        $panel = filament()->getPanel('admin');

        $this->assertTrue(User::factory()->admin()->create()->canAccessPanel($panel));
        $this->assertFalse(User::factory()->create()->canAccessPanel($panel));
    }

    public function test_normal_user_is_denied_the_admin_panel(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    // /admin redirects to the dashboard: our Dashboard is a plain Page, so it has a slug
    // rather than sitting at the panel root. Follow the redirect and assert we land.
    public function test_admin_reaches_the_panel(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertRedirect('/admin/dashboard');

        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/dashboard')
            ->assertSuccessful();
    }

    public function test_guest_is_sent_to_the_filament_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(filament()->getPanel('admin')->getLoginUrl());
    }

    // ---- course resource ----------------------------------------------------

    public function test_every_resource_index_renders(): void
    {
        $admin = User::factory()->admin()->create();
        $this->course();
        Instructor::factory()->create();

        foreach (['courses', 'instructors', 'users'] as $resource) {
            $this->actingAs($admin)->get("/admin/{$resource}")->assertSuccessful();
        }
    }

    public function test_users_resource_is_read_only(): void
    {
        $this->assertFalse(UserResource::canCreate());
        $this->assertFalse(UserResource::canEdit(User::factory()->create()));
        $this->assertFalse(UserResource::canDelete(User::factory()->create()));
    }

    // The infolist lists registrations.course.title. Without an eager load that is a course
    // query per registration, which preventLazyLoading turns into an exception.
    public function test_user_view_page_lists_registered_courses_without_lazy_loading(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();

        foreach (['Alpha Course', 'Beta Course', 'Gamma Course'] as $title) {
            $course = Course::factory()->create(['title' => $title, 'is_published' => true]);
            Registration::create(['user_id' => $student->id, 'course_id' => $course->id]);
        }

        $this->actingAs($admin)
            ->get(UserResource::getUrl('view', ['record' => $student]))
            ->assertSuccessful()
            ->assertSee('Alpha Course')
            ->assertSee('Beta Course')
            ->assertSee('Gamma Course');
    }

    public function test_online_course_is_saved_without_a_location(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)->test(CreateCourse::class)
            ->fillForm([
                'title' => 'Kubernetes Basics',
                'slug' => 'kubernetes-basics',
                'description' => 'Containers, pods and deployments.',
                'type' => 'webinar',
                'category' => 'cloud_computing',
                'format' => 'online',
                'meeting_link' => 'https://meet.example.com/k8s',
                'start_date' => today()->addWeek(),
                'end_date' => today()->addWeek()->addDay(),
                'registration_deadline' => today()->addDays(5),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('courses', [
            'slug' => 'kubernetes-basics',
            'format' => 'online',
            'meeting_link' => 'https://meet.example.com/k8s',
            'city' => null,
            'location' => null,
        ]);
    }

    public function test_flipping_a_course_from_offline_to_online_clears_the_location(): void
    {
        $admin = User::factory()->admin()->create();

        $course = Course::factory()->create([
            'format' => 'offline',
            'city' => 'erbil',
            'location' => 'ZARINALABS Erbil Campus',
            'meeting_link' => null,
        ]);

        Livewire::actingAs($admin)->test(EditCourse::class, ['record' => $course->id])
            ->fillForm([
                'format' => 'online',
                'meeting_link' => 'https://meet.example.com/moved',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $course->refresh();
        $this->assertSame('online', $course->format);
        $this->assertNull($course->city, 'the stale city must be cleared');
        $this->assertNull($course->location, 'the stale location must be cleared');
        $this->assertSame('https://meet.example.com/moved', $course->meeting_link);
    }

    public function test_the_visible_field_is_still_required(): void
    {
        $admin = User::factory()->admin()->create();

        $base = [
            'title' => 'Missing Field',
            'slug' => 'missing-field',
            'description' => 'x',
            'type' => 'course',
            'category' => 'networking',
            'start_date' => today()->addWeek(),
            'end_date' => today()->addWeek()->addDay(),
            'registration_deadline' => today()->addDays(5),
        ];

        // online without a meeting link
        Livewire::actingAs($admin)->test(CreateCourse::class)
            ->fillForm([...$base, 'format' => 'online'])
            ->call('create')
            ->assertHasFormErrors(['meeting_link']);

        // offline without a venue
        Livewire::actingAs($admin)->test(CreateCourse::class)
            ->fillForm([...$base, 'format' => 'offline'])
            ->call('create')
            ->assertHasFormErrors(['city', 'location']);

        $this->assertDatabaseCount('courses', 0);
    }

    public function test_video_url_must_be_youtube_or_vimeo(): void
    {
        $admin = User::factory()->admin()->create();

        $fill = fn (string $url) => Livewire::actingAs($admin)->test(CreateCourse::class)
            ->fillForm([
                'title' => 'Video Test',
                'slug' => 'video-test-'.Str::random(5),
                'description' => 'x',
                'video_url' => $url,
                'type' => 'course',
                'category' => 'networking',
                'format' => 'online',
                'meeting_link' => 'https://meet.example.com/v',
                'start_date' => today()->addWeek(),
                'end_date' => today()->addWeek()->addDay(),
                'registration_deadline' => today()->addDays(5),
            ])
            ->call('create');

        $fill('https://example.com/my-video.mp4')->assertHasFormErrors(['video_url']);
        $fill('https://www.youtube.com/watch?v=ImtZ5yENzgE')->assertHasNoFormErrors();
    }

    // ---- attendance matrix --------------------------------------------------

    public function test_attendance_page_renders_the_matrix_over_http(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create(['name' => 'Dilan Aziz']);
        $course = $this->course();
        CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()]);
        Registration::create(['user_id' => $student->id, 'course_id' => $course->id]);

        $this->actingAs($admin)->get("/admin/courses/{$course->id}/attendance")
            ->assertSuccessful()
            ->assertSee('Attendance: '.$course->title)
            ->assertSee('Dilan Aziz')
            ->assertSee('Student')
            ->assertSee('wire:click="toggle(', false);
    }

    public function test_attendance_page_handles_a_course_with_no_students(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->course();

        $this->actingAs($admin)->get("/admin/courses/{$course->id}/attendance")
            ->assertSuccessful()
            ->assertSee('Nothing to show yet');
    }

    public function test_attendance_matrix_toggles_a_mark_on_and_off(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->course();
        $session = CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()]);
        $registration = Registration::create(['user_id' => User::factory()->create()->id, 'course_id' => $course->id]);

        $page = Livewire::actingAs($admin)->test(AttendancePage::class, ['record' => $course->id]);

        $this->assertDatabaseCount('attendances', 0);

        $page->call('toggle', $registration->id, $session->id);
        $this->assertDatabaseHas('attendances', [
            'registration_id' => $registration->id,
            'course_session_id' => $session->id,
        ]);

        $page->call('toggle', $registration->id, $session->id);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_attendance_matrix_refuses_records_from_another_course(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->course();
        $other = $this->course();

        $otherSession = CourseSession::factory()->create(['course_id' => $other->id]);
        $registration = Registration::create(['user_id' => User::factory()->create()->id, 'course_id' => $course->id]);

        try {
            Livewire::actingAs($admin)
                ->test(AttendancePage::class, ['record' => $course->id])
                ->call('toggle', $registration->id, $otherSession->id);

            $this->fail('a session from another course should have been rejected');
        } catch (ModelNotFoundException) {
            // findOrFail scoped to this course: exactly what we want.
        }

        $this->assertDatabaseCount('attendances', 0);
    }

    // ---- QR check-in end to end ---------------------------------------------

    public function test_scanning_the_qr_marks_the_student_present(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $course = $this->course();
        $session = CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()]);
        $registration = Registration::create(['user_id' => $student->id, 'course_id' => $course->id]);

        // 1. The QR encodes a signed URL and the admin opens it.
        $url = $registration->checkInUrl();
        $this->actingAs($admin)->get($url)
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee($course->title)
            ->assertSee('Mark present');

        // 2. One tap.
        Livewire::actingAs($admin)
            ->test(CheckIn::class, ['registration' => $registration->uuid])
            ->assertSet('selectedSessionId', $session->id)   // today's session preselected
            ->call('markPresent')
            ->assertSee('Already checked in at');

        $this->assertDatabaseHas('attendances', [
            'registration_id' => $registration->id,
            'course_session_id' => $session->id,
        ]);
    }

    public function test_second_tap_does_not_duplicate_the_attendance(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->course();
        CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()]);
        $registration = Registration::create(['user_id' => User::factory()->create()->id, 'course_id' => $course->id]);

        $page = Livewire::actingAs($admin)->test(CheckIn::class, ['registration' => $registration->uuid]);
        $page->call('markPresent');
        $page->call('markPresent');

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_already_checked_in_shows_the_time_instead_of_a_button(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->course();
        $session = CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()]);
        $registration = Registration::create(['user_id' => User::factory()->create()->id, 'course_id' => $course->id]);

        Attendance::create([
            'registration_id' => $registration->id,
            'course_session_id' => $session->id,
            'checked_in_at' => today()->setTime(9, 7),
        ]);

        $this->actingAs($admin)->get($registration->checkInUrl())
            ->assertSee('Already checked in at 09:07')
            ->assertDontSee('Mark present');
    }

    public function test_no_session_today_warns_and_lets_the_admin_pick(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->course();
        CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()->subDays(3)]);
        CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()->addDays(3)]);
        $registration = Registration::create(['user_id' => User::factory()->create()->id, 'course_id' => $course->id]);

        $this->actingAs($admin)->get($registration->checkInUrl())
            ->assertOk()
            ->assertSee('No session is scheduled today')
            ->assertSee('Mark present');
    }

    public function test_check_in_rejects_a_session_from_another_course(): void
    {
        $admin = User::factory()->admin()->create();
        $course = $this->course();
        CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()]);
        $foreign = CourseSession::factory()->create(['course_id' => $this->course()->id]);
        $registration = Registration::create(['user_id' => User::factory()->create()->id, 'course_id' => $course->id]);

        try {
            Livewire::actingAs($admin)
                ->test(CheckIn::class, ['registration' => $registration->uuid])
                ->set('selectedSessionId', $foreign->id)
                ->call('markPresent');

            $this->fail('a session from another course should have been rejected');
        } catch (ModelNotFoundException) {
            // scoped findOrFail
        }

        $this->assertDatabaseCount('attendances', 0);
    }

}
