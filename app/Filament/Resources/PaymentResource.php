<?php

namespace App\Filament\Resources;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 20;

    public static function paymentStatusOptions(): array
    {
        return array_reduce(\App\Enums\PaymentStatus::cases(), fn (array $carry, \App\Enums\PaymentStatus $status) => $carry + [$status->value => $status->label()], []);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Payment')->schema([
                TextInput::make('booking_id')->numeric()->disabled(),
                TextInput::make('provider')->disabled(),
                TextInput::make('provider_payment_id')->disabled(),
                TextInput::make('provider_customer_id')->disabled(),
                Select::make('status')->options(self::paymentStatusOptions())->disabled(),
                TextInput::make('currency')->disabled(),
                TextInput::make('amount_minor')->numeric()->disabled(),
                TextInput::make('refunded_amount_minor')->numeric()->disabled(),
                TextInput::make('client_secret_last4')->disabled(),
                KeyValue::make('metadata')->disabled()->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking.booking_reference')->label('Booking')->searchable()->sortable(),
                TextColumn::make('provider')->badge()->sortable(),
                TextColumn::make('provider_payment_id')->searchable()->copyable()->limit(24),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state): string => $state instanceof PaymentStatus ? $state->label() : str((string) $state)->replace('_', ' ')->title()),
                TextColumn::make('amount_minor')->money(fn (Payment $record): string => $record->currency ?: 'USD', divideBy: 100)->sortable(),
                TextColumn::make('refunded_amount_minor')->money(fn (Payment $record): string => $record->currency ?: 'USD', divideBy: 100)->toggleable(),
                TextColumn::make('paid_at')->dateTime()->sortable(),
                TextColumn::make('failed_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(self::paymentStatusOptions()),
                SelectFilter::make('provider')->options(['stripe' => 'Stripe', 'mock_stripe' => 'Mock Stripe']),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->defaultSort('created_at', 'desc')
            ->poll('15s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
