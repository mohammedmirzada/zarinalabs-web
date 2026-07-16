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

class CourseResource extends Resource {

    protected static ?string $model = Course::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema {
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
                        Select::make('type')->options(config('options.course_types'))->required()->native(false),
                        Select::make('category')->options(config('options.categories'))->required()->native(false),
                        Select::make('instructor_id')
                            ->label('Instructor')
                            ->relationship('instructor', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ]),

                Section::make('Where')
                    ->columns(2)
                    ->schema([
                        Select::make('format')
                            ->options(config('options.formats'))
                            ->required()
                            ->live()
                            ->native(false)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state === 'online') {
                                    $set('city', null);
                                    $set('location', null);
                                } else {
                                    $set('meeting_link', null);
                                }
                            }),
                        TextInput::make('meeting_link')
                            ->url()
                            ->required(fn (Get $get) => $get('format') === 'online')
                            ->visible(fn (Get $get) => $get('format') === 'online')
                            ->dehydratedWhenHidden(),
                        Select::make('city')
                            ->options(config('options.cities'))
                            ->searchable()
                            ->native(false)
                            ->required(fn (Get $get) => $get('format') === 'offline')
                            ->visible(fn (Get $get) => $get('format') === 'offline')
                            ->dehydratedWhenHidden(),
                        TextInput::make('location')
                            ->label('Venue')
                            ->maxLength(255)
                            ->helperText('Building and street, e.g. ZARINALABS Erbil Campus, 100 Meter Road.')
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
                        Toggle::make('is_accepting')
                            ->default(true)
                            ->helperText('Off closes registration but keeps the course visible (shown greyed out).'),
                        Toggle::make('is_published')->helperText('Drafts are hidden from the public site.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => config('options.course_types')[$state] ?? $state),
                TextColumn::make('format')
                    ->formatStateUsing(fn (string $state) => config('options.formats')[$state] ?? $state),
                TextColumn::make('city')
                    ->formatStateUsing(fn (?string $state) => config('options.cities')[$state] ?? $state)
                    ->placeholder('Online'),
                TextColumn::make('start_date')->date('j M Y')->sortable(),
                TextColumn::make('sessions_count')->counts('sessions')->label('Sessions'),
                IconColumn::make('is_accepting')->boolean()->label('Accepting'),
                IconColumn::make('is_published')->boolean()->label('Published'),
            ])
            ->filters([
                SelectFilter::make('type')->options(config('options.course_types'))->native(false),
                SelectFilter::make('category')->options(config('options.categories'))->native(false),
                SelectFilter::make('city')->options(config('options.cities'))->native(false),
                SelectFilter::make('format')->options(config('options.formats'))->native(false),
                TernaryFilter::make('is_accepting')->label('Accepting')->native(false),
                TernaryFilter::make('is_published')->label('Published')->native(false)
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
