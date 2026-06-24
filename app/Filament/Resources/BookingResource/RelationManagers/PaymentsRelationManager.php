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

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('provider')->required()->maxLength(50),
            TextInput::make('provider_payment_id')->maxLength(255),
            Select::make('status')->options(\App\Filament\Resources\PaymentResource::paymentStatusOptions())->required(),
            TextInput::make('currency')->maxLength(3)->required(),
            TextInput::make('amount_minor')->numeric()->required(),
            TextInput::make('refunded_amount_minor')->numeric(),
            KeyValue::make('metadata')->columnSpanFull(),

        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('provider')->badge(),
                TextColumn::make('provider_payment_id')->searchable()->copyable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('amount_minor')->money(fn ($record): string => $record->currency ?: 'USD', divideBy: 100)->sortable(),
                TextColumn::make('refunded_amount_minor')->money(fn ($record): string => $record->currency ?: 'USD', divideBy: 100)->toggleable(),
                TextColumn::make('paid_at')->dateTime()->sortable(),
                TextColumn::make('failed_at')->dateTime()->toggleable(),

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
