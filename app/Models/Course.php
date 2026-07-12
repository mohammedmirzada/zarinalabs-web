<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title', 'slug', 'description', 'video_url', 'type', 'category', 'level',
    'instructor_id', 'format', 'meeting_link', 'location_id',
    'start_date', 'end_date', 'capacity', 'registration_deadline', 'is_published',
])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_deadline' => 'date',
            'is_published' => 'boolean',
        ];
    }

    /** @return BelongsTo<Instructor, $this> */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /** @return BelongsTo<Location, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** @return HasMany<CourseSession, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(CourseSession::class);
    }

    /** @return HasMany<Registration, $this> */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->whereDate('start_date', '>=', today());
    }

    /**
     * Uses registrations_count when the caller loaded it, so lists do not run a query per card.
     */
    public function seatsLeft(): int
    {
        $taken = $this->registrations_count ?? $this->registrations()->count();

        return max(0, $this->capacity - $taken);
    }

    public function isFull(): bool
    {
        return $this->seatsLeft() === 0;
    }

    /** The deadline is valid through the end of that day. */
    public function deadlinePassed(): bool
    {
        return today()->gt($this->registration_deadline);
    }

    /**
     * Turn the stored watch URL into an embeddable player URL. YouTube and Vimeo only.
     */
    public function embedUrl(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([\w-]{11})#', $this->video_url, $matches)) {
            return 'https://www.youtube-nocookie.com/embed/'.$matches[1];
        }

        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $this->video_url, $matches)) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }
}
