<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\CourseSession;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'course_session_id' => CourseSession::factory(),
            'checked_in_at' => now(),
        ];
    }
}
