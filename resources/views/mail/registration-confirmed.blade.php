<x-mail::message>
# You are registered

Hello {{ $user->name }},

Your seat on **{{ $course->title }}** is confirmed.

- **Type:** {{ config('options.course_types')[$course->type] }}
- **Dates:** {{ $course->start_date->format('j M Y') }} to {{ $course->end_date->format('j M Y') }}
- **Sessions:** {{ $course->sessions->count() }}
@if ($course->format === 'online')
- **Where:** Online
- **Meeting link:** [{{ $course->meeting_link }}]({{ $course->meeting_link }})
@else
- **Location:** {{ $course->location?->name }}, {{ $course->location?->address }}
@endif

Bring the QR code on your registrations page. We scan it at the door to mark you present.

<x-mail::button :url="route('my-registrations')">
View my registrations
</x-mail::button>
</x-mail::message>
