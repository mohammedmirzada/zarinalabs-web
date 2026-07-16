<?php

namespace App\Actions;

use App\Exceptions\RegistrationNotAllowed;
use App\Mail\RegistrationConfirmed;
use App\Models\Course;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RegisterUserForCourse
{
    /**
     * Every rule is enforced here, not by hiding the button.
     *
     * @throws RegistrationNotAllowed
     */
    public function execute(User $user, Course $course): Registration
    {
        if (! $user->hasVerifiedEmail()) {
            throw new RegistrationNotAllowed('Verify your email address before registering.');
        }

        if (! $course->is_published) {
            throw new RegistrationNotAllowed('This course is not open for registration.');
        }

        if (! $course->is_accepting) {
            throw new RegistrationNotAllowed('This course is not accepting registrations.');
        }

        if (today()->gt($course->registration_deadline)) {
            throw new RegistrationNotAllowed('The registration deadline has passed.');
        }

        // A transaction so the queued confirmation email is discarded if the insert rolls back.
        return DB::transaction(function () use ($user, $course) {
            try {
                $registration = Registration::create([
                    'user_id' => $user->getKey(),
                    'course_id' => $course->getKey(),
                ]);
            } catch (UniqueConstraintViolationException) {
                throw new RegistrationNotAllowed('You are already registered for this course.');
            }

            Mail::to($user)->queue(new RegistrationConfirmed($registration));

            return $registration;
        });
    }
}
