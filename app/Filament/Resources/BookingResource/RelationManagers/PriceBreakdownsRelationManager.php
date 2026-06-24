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

class PriceBreakdownsRelationManager extends RelationManager
{
    protected static string $relationship = 'priceBreakdowns';

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('label')->required()->maxLength(120),
            Select::make('type')->options(['base' => 'Base', 'tax' => 'Tax', 'fee' => 'Fee', 'markup' => 'Markup', 'discount' => 'Discount'])->required(),
            TextInput::make('currency')->maxLength(3)->required(),
            TextInput::make('amount_minor')->numeric()->required(),
            KeyValue::make('metadata')->columnSpanFull(),

        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('label')->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('currency'),
                TextColumn::make('amount_minor')->money(fn ($record): string => $record->currency ?: 'USD', divideBy: 100)->sortable(),
                TextColumn::make('created_at')->dateTime()->toggleable(),

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
