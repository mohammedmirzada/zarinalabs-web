<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(3), '.');
        $start = fake()->dateTimeBetween('+1 week', '+2 months');
        $end = (clone $start)->modify('+2 weeks');
        $deadline = (clone $start)->modify('-3 days');

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'video_url' => null,
            'type' => fake()->randomElement(array_keys(config('options.course_types'))),
            'category' => fake()->randomElement(array_keys(config('options.categories'))),
            'instructor_id' => Instructor::factory(),
            'format' => 'offline',
            'meeting_link' => null,
            'city' => fake()->randomElement(array_keys(config('options.cities'))),
            'location' => fake()->company().' Training Centre, '.fake()->streetAddress(),
            'start_date' => $start,
            'end_date' => $end,
            'registration_deadline' => $deadline,
            'is_accepting' => true,
            'is_published' => true,
        ];
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'online',
            'meeting_link' => 'https://meet.google.com/'.fake()->bothify('???-????-???'),
            'city' => null,
            'location' => null,
        ]);
    }

    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'offline',
            'meeting_link' => null,
            'city' => fake()->randomElement(array_keys(config('options.cities'))),
            'location' => fake()->company().' Training Centre, '.fake()->streetAddress(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_accepting' => false,
        ]);
    }
}
