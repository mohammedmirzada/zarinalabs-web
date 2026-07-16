<?php

namespace App\Livewire;

use App\Models\Course;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('ZARINALABS')]
class Home extends Component
{
    public function render()
    {
        return view('livewire.home', [
            'courses' => Course::published()->upcoming()
                ->with('instructor')
                ->orderBy('start_date')
                ->take(6)
                ->get(),
        ]);
    }
}
