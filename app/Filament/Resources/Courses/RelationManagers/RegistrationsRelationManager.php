<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Registrations';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('uuid')
            ->columns([
                TextColumn::make('user.name')->label('Student')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('user.phone')->label('Phone'),
                TextColumn::make('attendances_count')->counts('attendances')->label('Sessions attended'),
                TextColumn::make('created_at')->label('Registered')->dateTime('j M Y, H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            // Registrations are created by the student on the public site, never here.
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
