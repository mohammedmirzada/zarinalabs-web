<?php

namespace Tests\Feature;

use App\Actions\RegisterUserForCourse;
use App\Livewire\Courses\Index as CoursesIndex;
use App\Livewire\Courses\Show as CourseShow;
use App\Livewire\MyRegistrations;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Location;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    private function course(array $attributes = []): Course
    {
        return Course::factory()->create(array_merge([
            'is_published' => true,
            'capacity' => 2,
            'start_date' => today()->addDays(10),
            'end_date' => today()->addDays(12),
            'registration_deadline' => today()->addDays(5),
        ], $attributes));
    }

    // ---- home ---------------------------------------------------------------

    public function test_home_shows_upcoming_published_courses_only(): void
    {
        $this->course(['title' => 'Upcoming Thing']);
        $this->course(['title' => 'Draft Thing', 'is_published' => false]);
        $this->course([
            'title' => 'Past Thing',
            'start_date' => today()->subMonth(),
            'end_date' => today()->subWeeks(3),
            'registration_deadline' => today()->subMonths(2),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Upcoming Thing')
            ->assertDontSee('Draft Thing')
            ->assertDontSee('Past Thing');
    }

    // ---- course detail page -------------------------------------------------

    public function test_draft_course_detail_page_is_404(): void
    {
        $course = $this->course(['is_published' => false, 'slug' => 'hidden-course']);
        $this->get(route('courses.show', $course->slug))->assertNotFound();
    }

    // One assertion per test: actingAs() sticks for the whole test method.

    public function test_guest_is_told_to_log_in_to_register(): void
    {
        $this->course(['slug' => 'open-course']);
        $this->get(route('courses.show', 'open-course'))->assertSee('Log in to register');
    }

    public function test_unverified_user_is_told_to_verify(): void
    {
        $this->course(['slug' => 'open-course']);
        $this->actingAs(User::factory()->unverified()->create())
            ->get(route('courses.show', 'open-course'))->assertSee('Verify your email');
    }

    public function test_full_course_shows_full(): void
    {
        $this->course(['capacity' => 0, 'slug' => 'full-course']);
        $this->actingAs(User::factory()->create())
            ->get(route('courses.show', 'full-course'))->assertSee('Full');
    }

    public function test_closed_course_shows_deadline_passed(): void
    {
        $this->course(['registration_deadline' => today()->subDay(), 'slug' => 'late-course']);
        $this->actingAs(User::factory()->create())
            ->get(route('courses.show', 'late-course'))->assertSee('Deadline passed');
    }

    public function test_registered_user_sees_registered_state(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $course = $this->course(['slug' => 'open-course']);
        app(RegisterUserForCourse::class)->execute($user, $course);

        $this->actingAs($user)->get(route('courses.show', 'open-course'))
            ->assertSee('You are registered')
            ->assertSee('View my registrations');
    }

    public function test_video_url_is_turned_into_a_nocookie_embed(): void
    {
        $yt = $this->course(['video_url' => 'https://www.youtube.com/watch?v=ImtZ5yENzgE']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/ImtZ5yENzgE', $yt->embedUrl());

        $short = $this->course(['video_url' => 'https://youtu.be/ImtZ5yENzgE']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/ImtZ5yENzgE', $short->embedUrl());

        $vimeo = $this->course(['video_url' => 'https://vimeo.com/76979871']);
        $this->assertSame('https://player.vimeo.com/video/76979871', $vimeo->embedUrl());

        $this->assertNull($this->course(['video_url' => null])->embedUrl());
        $this->assertNull($this->course(['video_url' => 'https://example.com/nope'])->embedUrl());
    }

    // ---- no lazy loading (Model::preventLazyLoading is on in tests) ----------

    public function test_offline_course_detail_with_sessions_does_not_lazy_load(): void
    {
        $location = Location::factory()->create();
        $course = $this->course(['slug' => 'offline-with-sessions', 'format' => 'offline', 'location_id' => $location->id]);

        // location_id null: the session falls back to the course location
        CourseSession::factory()->count(3)->create(['course_id' => $course->id, 'location_id' => null]);
        CourseSession::factory()->create(['course_id' => $course->id, 'location_id' => Location::factory()]);

        $this->get(route('courses.show', 'offline-with-sessions'))->assertOk()->assertSee($location->name);
    }

    public function test_index_and_home_do_not_lazy_load(): void
    {
        Course::factory()->count(5)->create(['is_published' => true, 'start_date' => today()->addWeek()]);

        $this->get(route('home'))->assertOk();
        $this->get(route('courses.index'))->assertOk();
    }

    public function test_my_registrations_does_not_lazy_load(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        foreach (range(1, 3) as $i) {
            $course = $this->course(['slug' => "reg-course-{$i}"]);
            CourseSession::factory()->count(2)->create(['course_id' => $course->id]);
            app(RegisterUserForCourse::class)->execute($user, $course);
        }

        $this->actingAs($user)->get(route('my-registrations'))->assertOk();
    }

    // ---- filters ------------------------------------------------------------

    public function test_course_filters_narrow_the_list(): void
    {
        $erbil = Location::factory()->create(['city' => 'erbil']);
        $duhok = Location::factory()->create(['city' => 'duhok']);

        $this->course(['title' => 'Laravel Basics', 'category' => 'software_development', 'level' => 'beginner', 'location_id' => $erbil->id]);
        $this->course(['title' => 'Firewall Deep Dive', 'category' => 'cyber_security', 'level' => 'advanced', 'location_id' => $duhok->id]);

        Livewire::test(CoursesIndex::class)->assertSee('Laravel Basics')->assertSee('Firewall Deep Dive');

        Livewire::test(CoursesIndex::class)->set('search', 'Laravel')
            ->assertSee('Laravel Basics')->assertDontSee('Firewall Deep Dive');

        Livewire::test(CoursesIndex::class)->set('category', 'cyber_security')
            ->assertSee('Firewall Deep Dive')->assertDontSee('Laravel Basics');

        Livewire::test(CoursesIndex::class)->set('city', 'erbil')
            ->assertSee('Laravel Basics')->assertDontSee('Firewall Deep Dive');

        Livewire::test(CoursesIndex::class)->set('level', 'advanced')
            ->assertSee('Firewall Deep Dive')->assertDontSee('Laravel Basics');
    }

    public function test_index_hides_drafts_and_past_courses_by_default(): void
    {
        $this->course(['title' => 'Draft Course', 'is_published' => false]);
        $this->course(['title' => 'Past Course', 'start_date' => today()->subMonth(), 'end_date' => today()->subWeeks(3), 'registration_deadline' => today()->subMonths(2)]);
        $this->course(['title' => 'Future Course']);

        Livewire::test(CoursesIndex::class)
            ->assertSee('Future Course')
            ->assertDontSee('Draft Course')
            ->assertDontSee('Past Course');
    }

    // ---- my registrations + QR ---------------------------------------------

    public function test_my_registrations_renders_a_qr_code_and_attendance(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $course = $this->course(['start_date' => today()->subDays(10), 'end_date' => today()->subDays(3), 'registration_deadline' => today()->subDays(12)]);

        $past = CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()->subDays(10)]);
        $absent = CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()->subDays(5)]);
        CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()->addDays(5)]);

        $registration = Registration::create(['user_id' => $user->id, 'course_id' => $course->id]);
        Attendance::create(['registration_id' => $registration->id, 'course_session_id' => $past->id, 'checked_in_at' => now()]);

        $response = Livewire::actingAs($user)->test(MyRegistrations::class);

        $response->assertSee('Present')->assertSee('Absent')->assertSee('Upcoming');
        $response->assertSee('<svg', false);  // the QR rendered server side
    }

    public function test_my_registrations_requires_a_verified_login(): void
    {
        $this->get(route('my-registrations'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->unverified()->create())
            ->get(route('my-registrations'))->assertRedirect(route('verification.notice'));
    }
}
