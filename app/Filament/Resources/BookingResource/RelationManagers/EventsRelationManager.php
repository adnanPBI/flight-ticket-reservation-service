<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('from_status')->disabled(),
            TextInput::make('to_status')->disabled(),
            TextInput::make('reason')->disabled(),
            KeyValue::make('metadata')->disabled()->columnSpanFull(),

        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('from_status')->badge(),
                TextColumn::make('to_status')->badge(),
                TextColumn::make('reason')->wrap()->toggleable(),
                TextColumn::make('actor_user_id')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable(),

            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
