<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseSession>
 */
class CourseSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'session_date' => fake()->dateTimeBetween('+1 week', '+2 months'),
            'start_time' => '10:00:00',
            'end_time' => '13:00:00',
            'location_id' => null,  // falls back to the course location
        ];
    }
}
