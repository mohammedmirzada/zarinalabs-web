<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserResource extends Resource {

    protected static ?string $model = User::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;
    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                        TextEntry::make('phone'),
                        TextEntry::make('gender')
                            ->formatStateUsing(fn (string $state) => config('options.genders')[$state] ?? $state),
                        TextEntry::make('date_of_birth')->date('j F Y'),
                        TextEntry::make('city')
                            ->formatStateUsing(fn (string $state) => config('options.cities')[$state] ?? $state),
                        TextEntry::make('education_level')
                            ->formatStateUsing(fn (string $state) => config('options.education_levels')[$state] ?? $state),
                        TextEntry::make('it_interest')
                            ->label('IT interest')
                            ->formatStateUsing(fn (string $state) => config('options.it_interests')[$state] ?? $state),
                        TextEntry::make('created_at')->label('Joined')->date('j M Y'),
                    ]),

                Section::make('Registrations')
                    ->schema([
                        TextEntry::make('registrations.course.title')
                            ->label('Courses')
                            ->listWithLineBreaks()
                            ->placeholder('No registrations yet'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('registrations'))
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('city')
                    ->formatStateUsing(fn (string $state) => config('options.cities')[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('registrations_count')->label('Registrations')->sortable(),
                IconColumn::make('is_admin')->boolean()->label('Admin'),
            ])
            ->filters([
                SelectFilter::make('city')->options(config('options.cities')),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'view' => ViewUser::route('/{record}'),
        ];
    }
}
