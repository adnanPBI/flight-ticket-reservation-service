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

class PassengersRelationManager extends RelationManager
{
    protected static string $relationship = 'passengers';

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Select::make('passenger_type')->options(['adult' => 'Adult', 'child' => 'Child', 'infant' => 'Infant'])->required(),
            TextInput::make('title')->maxLength(20),
            TextInput::make('first_name')->required()->maxLength(100),
            TextInput::make('last_name')->required()->maxLength(100),
            DatePicker::make('date_of_birth')->required(),
            Select::make('gender')->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])->nullable(),
            TextInput::make('nationality')->maxLength(2),
            TextInput::make('passport_number')->maxLength(100),
            DatePicker::make('passport_expiry_date'),

        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('passenger_type')->badge(),
                TextColumn::make('title')->toggleable(),
                TextColumn::make('first_name')->searchable(),
                TextColumn::make('last_name')->searchable(),
                TextColumn::make('date_of_birth')->date(),
                TextColumn::make('nationality')->toggleable(),
                TextColumn::make('passport_number')->label('Passport')->placeholder('Hidden')->toggleable(isToggledHiddenByDefault: true),

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
