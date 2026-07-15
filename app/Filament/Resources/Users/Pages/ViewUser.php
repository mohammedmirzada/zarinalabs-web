<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    // The infolist reads registrations.course.title, which would lazy load a course per
    // registration. Eager load it here rather than on the table query, which only counts.
    protected function resolveRecord(int|string $key): Model
    {
        return parent::resolveRecord($key)->load('registrations.course');
    }

    // Read only: no edit action.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
