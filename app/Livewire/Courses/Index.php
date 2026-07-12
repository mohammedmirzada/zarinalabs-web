<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Courses and events')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $city = '';

    #[Url]
    public string $level = '';

    /** Empty means "from today", i.e. upcoming only. */
    #[Url]
    public string $date = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'category', 'city', 'level', 'date'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'category', 'city', 'level', 'date');
        $this->resetPage();
    }

    public function hasFilters(): bool
    {
        return $this->search !== '' || $this->category !== '' || $this->city !== ''
            || $this->level !== '' || $this->date !== '';
    }

    public function render()
    {
        $courses = Course::published()
            ->with(['instructor', 'location'])
            ->withCount('registrations')
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->category, fn ($query) => $query->where('category', $this->category))
            ->when($this->level, fn ($query) => $query->where('level', $this->level))
            ->when($this->city, fn ($query) => $query->whereHas('location',
                fn ($location) => $location->where('city', $this->city)))
            ->whereDate('start_date', '>=', $this->date ?: today())
            ->orderBy('start_date')
            ->paginate(9);

        return view('livewire.courses.index', ['courses' => $courses]);
    }
}
