<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Instructor;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

// No WithoutModelEvents here: Registration fills its uuid on the `creating` event.
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createAdmin();

        $instructors = $this->createInstructors();
        $this->createCourses($instructors);
        $this->createStudentsWithRegistrations();
    }

    private function createAdmin(): void
    {
        User::factory()->admin()->create([
            'name' => 'ZARINALABS Admin',
            'email' => 'admin@zarinalabs.com',
            'password' => '1234567890',
            'city' => 'erbil',
        ]);
    }

    /** @return Collection<int, Instructor> */
    private function createInstructors(): Collection
    {
        $people = [
            ['Rebaz Ahmed', 'Cloud architect with twelve years building data platforms for banks and telecoms across Iraq. Teaches AWS, data engineering and machine learning.'],
            ['Sara Kareem', 'Product designer who has shipped design systems for three Iraqi startups. Focuses on research, usability testing and accessible interfaces.'],
            ['Hemin Salih', 'Network engineer and penetration tester. Holds CCIE and OSCP, and runs the security operations centre for a regional ISP.'],
            ['Noor Abdullah', 'Backend engineer specialising in PHP and Laravel. Maintains several open source packages and mentors junior developers in Erbil.'],
        ];

        return collect($people)->map(function (array $person) {
            [$name, $bio] = $person;

            return Instructor::create([
                'name' => $name,
                'photo_path' => $this->writePlaceholderPhoto($name),
                'bio' => $bio,
            ]);
        });
    }

    /**
     * Instructor photos are placeholder initials on a rounded square, written to the public disk.
     */
    private function writePlaceholderPhoto(string $name): string
    {
        $initials = collect(explode(' ', $name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
        $path = 'instructors/'.str($name)->slug().'.svg';

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">
            <rect width="200" height="200" rx="16" fill="#173B45"/>
            <text x="100" y="100" fill="#FBF9FA" font-family="Inter, sans-serif" font-size="72"
                  text-anchor="middle" dominant-baseline="central">{$initials}</text>
        </svg>
        SVG;

        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    /**
     * Ten published courses covering all five types and both formats.
     * `start` is an offset in days from today; sessions step forward by `every` days.
     * `location` indexes into $venues below (city + venue text), or null for online courses.
     */
    private function createCourses(Collection $instructors): void
    {
        $venues = [
            ['city' => 'erbil', 'location' => 'ZARINALABS Erbil Campus, 100 Meter Road, near Family Mall'],
            ['city' => 'sulaymaniyah', 'location' => 'Lions Fort Sulaymaniyah Hub, Salim Street, Sulaymaniyah Tower'],
            ['city' => 'duhok', 'location' => 'Duhok Innovation Centre, Barzan Street, Block 4'],
            ['city' => 'baghdad', 'location' => 'Baghdad Tech Space, Karrada, Al Sadoon Street'],
        ];

        $specs = [
            [
                'title' => 'Laravel Fundamentals', 'type' => 'course', 'category' => 'software_development',
                'format' => 'offline', 'instructor' => 3, 'location' => 0,
                'start' => -21, 'sessions' => 6, 'every' => 7,
                'video_url' => 'https://www.youtube.com/watch?v=ImtZ5yENzgE',
                'description' => "Build a real web application from an empty folder to a deployed site. We cover routing, Eloquent, Blade, validation and testing, one working feature at a time.\n\nYou need basic PHP. You do not need any framework experience.",
            ],
            [
                'title' => 'CCNA Networking Bootcamp', 'type' => 'course', 'category' => 'networking',
                'format' => 'offline', 'instructor' => 2, 'location' => 1,
                'start' => -49, 'sessions' => 7, 'every' => 7,
                'description' => "Seven full days of switching, routing, subnetting and troubleshooting on real hardware. Every session ends with a lab you must finish before you leave.\n\nAimed at engineers already working in a network operations role.",
            ],
            [
                'title' => 'UI/UX Design Sprint', 'type' => 'workshop', 'category' => 'ui_ux_design',
                'format' => 'offline', 'instructor' => 1, 'location' => 2,
                'start' => -14, 'sessions' => 3, 'every' => 5,
                'description' => "Three sessions, one product idea, one tested prototype. You will interview users, sketch, build in Figma and run a usability test on the last day.\n\nBring a laptop. No design background required.",
            ],
            [
                'title' => 'Network Security Workshop', 'type' => 'workshop', 'category' => 'cyber_security',
                'format' => 'offline', 'instructor' => 2, 'location' => 1,
                'start' => 10, 'sessions' => 2, 'every' => 1,
                'description' => "Two days of hands-on defence: hardening a switch, reading packet captures, spotting lateral movement and writing detection rules that do not drown you in noise.\n\nComfort with the Linux command line is assumed.",
            ],
            [
                'title' => 'Intro to Cloud on AWS', 'type' => 'webinar', 'category' => 'cloud_computing',
                'format' => 'online', 'instructor' => 0, 'location' => null,
                'start' => 14, 'sessions' => 1, 'every' => 1,
                'description' => "A single ninety minute session explaining what the cloud actually is, what EC2, S3 and RDS do, and what they cost. Live demo, questions welcome throughout.",
            ],
            [
                'title' => 'Cyber Security Awareness', 'type' => 'seminar', 'category' => 'cyber_security',
                'format' => 'online', 'instructor' => 2, 'location' => null, 'is_accepting' => false,
                'start' => 21, 'sessions' => 1, 'every' => 1,
                'description' => "Phishing, passwords, and the handful of habits that stop most attacks. Written for everyone in an office, not just the IT team.",
            ],
            [
                'title' => 'Database Design Seminar', 'type' => 'seminar', 'category' => 'database',
                'format' => 'offline', 'instructor' => 0, 'location' => 3,
                'start' => 28, 'sessions' => 2, 'every' => 1,
                'description' => "Normalisation, indexing and the queries that quietly destroy a production database. We look at real schemas and fix them together.\n\nBring a schema you are unhappy with.",
            ],
            [
                'title' => 'Zarina Tech Meetup', 'type' => 'event', 'category' => 'other',
                'format' => 'offline', 'instructor' => null, 'location' => 0,
                'start' => 35, 'sessions' => 1, 'every' => 1,
                'description' => "An evening of short talks from people building software in Kurdistan, followed by food and conversation. Open to everyone, whatever your level.",
            ],
            [
                'title' => 'AI for Beginners', 'type' => 'course', 'category' => 'artificial_intelligence',
                'format' => 'online', 'instructor' => 0, 'location' => null,
                'start' => 42, 'sessions' => 8, 'every' => 7,
                'video_url' => 'https://vimeo.com/76979871',
                'description' => "Eight weekly sessions from linear regression to a working neural network, in plain Python. We build every model by hand before reaching for a library.\n\nSchool level mathematics is enough.",
            ],
            [
                'title' => 'Data Science with Python', 'type' => 'course', 'category' => 'data_science',
                'format' => 'online', 'instructor' => 0, 'location' => null,
                'start' => 49, 'sessions' => 6, 'every' => 7,
                'description' => "Pandas, feature engineering, model selection and the parts of a data project that actually take the time: cleaning, leakage and honest evaluation.\n\nYou should already write Python comfortably.",
            ],
        ];

        foreach ($specs as $spec) {
            $start = today()->addDays($spec['start']);
            $end = $start->copy()->addDays(($spec['sessions'] - 1) * $spec['every']);
            $venue = $spec['location'] === null ? null : $venues[$spec['location']];

            $course = Course::create([
                'title' => $spec['title'],
                'slug' => str($spec['title'])->slug(),
                'description' => $spec['description'],
                'video_url' => $spec['video_url'] ?? null,
                'type' => $spec['type'],
                'category' => $spec['category'],
                'instructor_id' => $spec['instructor'] === null ? null : $instructors[$spec['instructor']]->id,
                'format' => $spec['format'],
                'meeting_link' => $spec['format'] === 'online'
                    ? 'https://meet.google.com/'.str($spec['title'])->slug()
                    : null,
                'city' => $venue['city'] ?? null,
                'location' => $venue['location'] ?? null,
                'start_date' => $start,
                'end_date' => $end,
                'registration_deadline' => $start->copy()->subDays(3),
                'is_accepting' => $spec['is_accepting'] ?? true,
                'is_published' => true,
            ]);

            for ($i = 0; $i < $spec['sessions']; $i++) {
                CourseSession::create([
                    'course_id' => $course->id,
                    'session_date' => $start->copy()->addDays($i * $spec['every']),
                    'start_time' => '10:00:00',
                    'end_time' => '13:00:00',
                ]);
            }
        }
    }

    /**
     * Fifteen verified students, each registered on one to three courses,
     * with attendance recorded for most sessions that have already happened.
     */
    private function createStudentsWithRegistrations(): void
    {
        $courses = Course::with('sessions')->get();

        User::factory()->count(15)->create()->each(function (User $user) use ($courses) {
            $chosen = Collection::wrap($courses->random(random_int(1, 3)));

            foreach ($chosen as $course) {
                $registration = Registration::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ]);

                foreach ($course->sessions as $session) {
                    if ($session->session_date->lt(today()) && random_int(1, 10) <= 7) {
                        Attendance::create([
                            'registration_id' => $registration->id,
                            'course_session_id' => $session->id,
                            'checked_in_at' => $session->session_date
                                ->copy()
                                ->setTimeFromTimeString($session->start_time)
                                ->addMinutes(random_int(0, 20)),
                        ]);
                    }
                }
            }
        });
    }
}
