<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Courses', Course::count())
                ->description('Courses and events')
                ->descriptionIcon('heroicon-s-academic-cap')
                ->color('primary'),

            Stat::make('Registered users', User::where('is_admin', false)->count())
                ->description('People with an account')
                ->descriptionIcon('heroicon-s-users')
                ->color('primary'),

            Stat::make('Instructors', Instructor::count())
                ->description('People who teach')
                ->descriptionIcon('heroicon-s-user-group')
                ->color('primary'),
        ];
    }
}
