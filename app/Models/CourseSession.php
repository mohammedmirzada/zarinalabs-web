<?php

namespace App\Models;

use Database\Factories\CourseSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

// Named course_sessions because Laravel already owns the `sessions` table.
#[Fillable(['course_id', 'session_date', 'start_time', 'end_time', 'location_id'])]
class CourseSession extends Model
{
    /** @use HasFactory<CourseSessionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
        ];
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return BelongsTo<Location, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** @return HasMany<Attendance, $this> */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /** Times are stored as raw TIME values, so format them here rather than casting. */
    public function timeRange(): string
    {
        $start = Carbon::parse($this->start_time)->format('g:i A');

        if (! $this->end_time) {
            return $start;
        }

        return $start.' - '.Carbon::parse($this->end_time)->format('g:i A');
    }

    public function isPast(): bool
    {
        return $this->session_date->lt(today());
    }
}
