<?php

/**
 * The paths where a bug costs someone a seat, a place at the door, or their privacy.
 * Everything here goes through the real action or the real route, never a shortcut.
 */

use App\Actions\RegisterUserForCourse;
use App\Exceptions\RegistrationNotAllowed;
use App\Mail\RegistrationConfirmed;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

function openCourse(array $overrides = []): Course
{
    return Course::factory()->create(array_merge([
        'is_published' => true,
        'is_accepting' => true,
        'start_date' => today()->addDays(10),
        'end_date' => today()->addDays(12),
        'registration_deadline' => today()->addDays(5),
    ], $overrides));
}

function register(User $user, Course $course): Registration
{
    return app(RegisterUserForCourse::class)->execute($user, $course);
}

// ---- registration rules -----------------------------------------------------

it('registers a verified user, mints a uuid, and queues the confirmation email', function () {
    $user = User::factory()->create();
    $course = openCourse();

    $registration = register($user, $course);

    expect($registration->uuid)->toHaveLength(36)
        ->and($registration->course_id)->toBe($course->id)
        ->and($registration->user_id)->toBe($user->id);

    Mail::assertQueued(RegistrationConfirmed::class, fn ($mail) => $mail->hasTo($user->email));
});

it('refuses a user who has not verified their email', function () {
    register(User::factory()->unverified()->create(), openCourse());
})->throws(RegistrationNotAllowed::class, 'Verify your email address before registering.');

it('refuses a course that is still a draft', function () {
    register(User::factory()->create(), openCourse(['is_published' => false]));
})->throws(RegistrationNotAllowed::class, 'This course is not open for registration.');

it('refuses once the registration deadline has passed', function () {
    register(User::factory()->create(), openCourse(['registration_deadline' => today()->subDay()]));
})->throws(RegistrationNotAllowed::class, 'The registration deadline has passed.');

it('still accepts a registration on the deadline day itself', function () {
    $registration = register(User::factory()->create(), openCourse(['registration_deadline' => today()]));

    expect($registration->exists)->toBeTrue();
});

it('rejects a closed course even when the button is bypassed', function () {
    // The action is the gate, not the blade template.
    $closed = openCourse(['is_accepting' => false]);

    expect(fn () => register(User::factory()->create(), $closed))
        ->toThrow(RegistrationNotAllowed::class, 'This course is not accepting registrations.');
});

// ---- duplicate registration -------------------------------------------------

it('blocks the same user registering twice for the same course', function () {
    $user = User::factory()->create();
    $course = openCourse();

    register($user, $course);

    expect(fn () => register($user, $course))->toThrow(RegistrationNotAllowed::class);
    expect(Registration::where('user_id', $user->id)->where('course_id', $course->id)->count())->toBe(1);
});

it('has a database unique index behind the duplicate check', function () {
    $user = User::factory()->create();
    $course = openCourse();
    register($user, $course);

    // Straight past the action, into the table.
    expect(fn () => Registration::create(['user_id' => $user->id, 'course_id' => $course->id]))
        ->toThrow(Illuminate\Database\UniqueConstraintViolationException::class);
});

// ---- signed check-in --------------------------------------------------------

it('lets an admin check a student in through the signed QR link', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->create();
    $course = openCourse(['start_date' => today(), 'registration_deadline' => today()]);
    $session = CourseSession::factory()->create(['course_id' => $course->id, 'session_date' => today()]);

    $registration = register($student, $course);

    $this->actingAs($admin)
        ->get($registration->checkInUrl())
        ->assertOk()
        ->assertSee($student->name)
        ->assertSee('Mark present');

    expect($registration->attendances()->count())->toBe(0)
        ->and($session->course_id)->toBe($course->id);
});

it('refuses a check-in link that is unsigned, tampered with, or opened by the wrong person', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->create();
    $course = openCourse();
    $registration = register($student, $course);

    $signed = $registration->checkInUrl();

    // Guest first: actingAs() sticks for the rest of the test.
    $this->get($signed)->assertRedirect(route('login'));

    $this->actingAs($admin)->get(route('admin.check-in', ['registration' => $registration->uuid]))->assertForbidden();
    $this->actingAs($admin)->get($signed.'&extra=1')->assertForbidden();
    $this->actingAs($student)->get($signed)->assertForbidden();
});

it('never shows the meeting link to anyone who is not registered', function () {
    $course = openCourse(['format' => 'online', 'meeting_link' => 'https://meet.example.com/private-room', 'city' => null, 'location' => null]);
    $user = User::factory()->create();

    $this->get(route('courses.show', $course->slug))->assertDontSee('private-room');
    $this->actingAs($user)->get(route('courses.show', $course->slug))->assertDontSee('private-room');

    register($user, $course);

    $this->actingAs($user)->get(route('courses.show', $course->slug))->assertSee('private-room');
});
