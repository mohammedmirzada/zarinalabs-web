<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\CourseSession;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    protected static ?string $title = 'Sessions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('session_date')->required(),
                TimePicker::make('start_time')->seconds(false)->required(),
                TimePicker::make('end_time')->seconds(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('session_date')
            ->columns([
                TextColumn::make('session_date')->date('D, j M Y')->sortable(),
                TextColumn::make('times')
                    ->label('Time')
                    ->state(fn (CourseSession $record) => $record->timeRange()),
                TextColumn::make('attendances_count')->counts('attendances')->label('Present'),
            ])
            ->defaultSort('session_date')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
