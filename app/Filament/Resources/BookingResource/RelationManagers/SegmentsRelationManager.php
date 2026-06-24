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

class SegmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'segments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('segment_order')->numeric()->required(),
            TextInput::make('airline_code')->maxLength(10),
            TextInput::make('flight_number')->maxLength(20),
            TextInput::make('origin')->maxLength(3)->required(),
            TextInput::make('destination')->maxLength(3)->required(),
            TextInput::make('aircraft')->maxLength(100),
            TextInput::make('booking_class')->maxLength(50),
            TextInput::make('cabin_class')->maxLength(50),
            TextInput::make('status')->maxLength(50),

        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('segment_order')->sortable(),
                TextColumn::make('airline_code')->searchable(),
                TextColumn::make('flight_number')->searchable(),
                TextColumn::make('origin')->searchable(),
                TextColumn::make('destination')->searchable(),
                TextColumn::make('departure_at')->dateTime()->sortable(),
                TextColumn::make('arrival_at')->dateTime()->sortable(),
                TextColumn::make('cabin_class')->badge(),
                TextColumn::make('status')->badge(),

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
