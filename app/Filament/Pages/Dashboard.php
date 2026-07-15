<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.dashboard';

    /** @return array<class-string> */
    public function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }
}
