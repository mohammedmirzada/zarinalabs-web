<?php

namespace Tests\Feature;

use App\Actions\RegisterUserForCourse;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * An N+1 shows up as a query count that grows with the number of rows on the page.
 * Model::preventLazyLoading catches most of it; this catches the rest.
 */
class QueryBudgetTest extends TestCase
{
    use RefreshDatabase;

    private function queries(callable $request): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $request();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    private function seedCourses(int $howMany): void
    {
        Course::factory()->count($howMany)->offline()->create([
            'is_published' => true,
            'start_date' => today()->addWeek(),
            'end_date' => today()->addWeeks(2),
            'registration_deadline' => today()->addDays(3),
        ])->each(fn (Course $course) => CourseSession::factory()->count(3)->create(['course_id' => $course->id]));
    }

    public function test_courses_index_queries_do_not_grow_with_the_number_of_cards(): void
    {
        $this->seedCourses(2);
        $withTwo = $this->queries(fn () => $this->get(route('courses.index'))->assertOk());

        $this->seedCourses(7);
        $withNine = $this->queries(fn () => $this->get(route('courses.index'))->assertOk());

        $this->assertSame($withTwo, $withNine, "courses index went from {$withTwo} to {$withNine} queries");
        $this->assertLessThan(10, $withNine);
    }

    public function test_home_queries_do_not_grow_with_the_number_of_items(): void
    {
        $this->seedCourses(1);
        $small = $this->queries(fn () => $this->get(route('home'))->assertOk());

        $this->seedCourses(5);
        $large = $this->queries(fn () => $this->get(route('home'))->assertOk());

        $this->assertSame($small, $large, "home went from {$small} to {$large} queries");
        $this->assertLessThan(10, $large);
    }

    public function test_my_registrations_queries_do_not_grow_with_the_number_of_registrations(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $this->seedCourses(6);
        $courses = Course::all();

        app(RegisterUserForCourse::class)->execute($user, $courses[0]);
        $withOne = $this->queries(fn () => $this->actingAs($user)->get(route('my-registrations'))->assertOk());

        foreach ($courses->skip(1) as $course) {
            app(RegisterUserForCourse::class)->execute($user, $course);
        }
        $withSix = $this->queries(fn () => $this->actingAs($user)->get(route('my-registrations'))->assertOk());

        $this->assertSame($withOne, $withSix, "my registrations went from {$withOne} to {$withSix} queries");
        $this->assertLessThan(10, $withSix);
    }

    public function test_course_detail_queries_do_not_grow_with_the_number_of_sessions(): void
    {
        $course = Course::factory()->offline()->create([
            'is_published' => true, 'slug' => 'budget-course',
            'start_date' => today()->addWeek(), 'end_date' => today()->addWeeks(2),
            'registration_deadline' => today()->addDays(3),
        ]);

        CourseSession::factory()->count(2)->create(['course_id' => $course->id]);
        $withTwo = $this->queries(fn () => $this->get(route('courses.show', 'budget-course'))->assertOk());

        CourseSession::factory()->count(8)->create(['course_id' => $course->id]);
        $withTen = $this->queries(fn () => $this->get(route('courses.show', 'budget-course'))->assertOk());

        $this->assertSame($withTwo, $withTen, "course detail went from {$withTwo} to {$withTen} queries");
        $this->assertLessThan(10, $withTen);
    }
}
