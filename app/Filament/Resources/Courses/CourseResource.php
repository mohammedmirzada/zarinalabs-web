<?php

namespace App\Filament\Resources\Courses;

use App\Filament\Resources\Courses\Pages\Attendance;
use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Filament\Resources\Courses\RelationManagers\RegistrationsRelationManager;
use App\Filament\Resources\Courses\RelationManagers\SessionsRelationManager;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Textarea::make('description')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),

                        TextInput::make('video_url')
                            ->label('Intro video URL')
                            ->url()
                            ->helperText('YouTube or Vimeo only. Leave empty for no video.')
                            ->rules(['nullable', 'regex:#^https?://(www\.)?(youtube\.com/watch\?v=|youtu\.be/|vimeo\.com/)#'])
                            ->columnSpanFull(),
                    ]),

                Section::make('Classification')
                    ->columns(2)
                    ->schema([
                        Select::make('type')->options(config('options.course_types'))->required(),
                        Select::make('category')->options(config('options.categories'))->required(),
                        Select::make('level')->options(config('options.levels'))->required(),
                        Select::make('instructor_id')
                            ->label('Instructor')
                            ->relationship('instructor', 'name')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Where')
                    ->columns(2)
                    ->schema([
                        Select::make('format')
                            ->options(config('options.formats'))
                            ->required()
                            ->live()
                            // Clear whichever field no longer applies, so a course flipped from
                            // offline to online does not keep a stale location, and vice versa.
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                $set($state === 'online' ? 'location_id' : 'meeting_link', null);
                            }),

                        // Exactly one of these shows, and the one that shows is the required one.
                        // dehydratedWhenHidden keeps the null of the hidden one, which Filament
                        // would otherwise drop from the save payload — but it also means the
                        // hidden field is validated, so required() has to be conditional too.
                        TextInput::make('meeting_link')
                            ->url()
                            ->required(fn (Get $get) => $get('format') === 'online')
                            ->visible(fn (Get $get) => $get('format') === 'online')
                            ->dehydratedWhenHidden(),

                        Select::make('location_id')
                            ->label('Location')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get) => $get('format') === 'offline')
                            ->visible(fn (Get $get) => $get('format') === 'offline')
                            ->dehydratedWhenHidden(),
                    ]),

                Section::make('Schedule')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('start_date')->required(),
                        DatePicker::make('end_date')->required()->afterOrEqual('start_date'),
                        DatePicker::make('registration_deadline')->required()->beforeOrEqual('start_date'),
                        TextInput::make('capacity')->required()->numeric()->minValue(1),
                        Toggle::make('is_published')->helperText('Drafts are hidden from the public site.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('registrations'))
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => config('options.course_types')[$state] ?? $state),

                TextColumn::make('level')
                    ->formatStateUsing(fn (string $state) => config('options.levels')[$state] ?? $state),

                TextColumn::make('format')
                    ->formatStateUsing(fn (string $state) => config('options.formats')[$state] ?? $state),

                TextColumn::make('location.name')->placeholder('Online')->toggleable(),

                TextColumn::make('start_date')->date('j M Y')->sortable(),

                TextColumn::make('seats')
                    ->label('Seats left')
                    ->state(fn (Course $record) => $record->seatsLeft().' of '.$record->capacity),

                TextColumn::make('sessions_count')->counts('sessions')->label('Sessions'),

                IconColumn::make('is_published')->boolean()->label('Published'),
            ])
            ->filters([
                SelectFilter::make('type')->options(config('options.course_types')),
                SelectFilter::make('category')->options(config('options.categories')),
                SelectFilter::make('level')->options(config('options.levels')),
                SelectFilter::make('format')->options(config('options.formats')),
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->defaultSort('start_date', 'desc')
            ->recordActions([
                Action::make('attendance')
                    ->icon(Heroicon::ClipboardDocumentCheck)
                    ->url(fn (Course $record) => Attendance::getUrl(['record' => $record])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SessionsRelationManager::class,
            RegistrationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'edit' => EditCourse::route('/{record}/edit'),
            'attendance' => Attendance::route('/{record}/attendance'),
        ];
    }
}
