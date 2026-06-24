<?php

namespace App\Filament\Resources;

use App\Actions\Admin\MarkBookingRefundPendingAction;
use App\Actions\Admin\RecordManualTicketIssuedAction;
use App\Actions\Admin\RetryBookingFinalizationAction;
use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';
    protected static string|\UnitEnum|null $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Booking identity')->schema([
                TextInput::make('booking_reference')->disabled(),
                Select::make('status')->options(array_reduce(\App\Enums\BookingStatus::cases(), fn (array $carry, \App\Enums\BookingStatus $status) => $carry + [$status->value => $status->label()], []))->disabled(),
                Select::make('provider')->options(array_reduce(\App\Enums\FlightProvider::cases(), fn (array $carry, \App\Enums\FlightProvider $provider) => $carry + [$provider->value => strtoupper($provider->value)], []))->disabled(),
                TextInput::make('provider_offer_id')->disabled(),
                TextInput::make('provider_order_id')->disabled(),
                TextInput::make('provider_order_status')->disabled(),
                TextInput::make('pnr')->maxLength(100),
                TextInput::make('ticket_number')->maxLength(100),
            ])->columns(3),
            Section::make('Customer and price')->schema([
                TextInput::make('customer_email')->email()->maxLength(255),
                TextInput::make('customer_phone')->maxLength(50),
                TextInput::make('currency')->disabled(),
                TextInput::make('provider_base_amount_minor')->numeric()->disabled(),
                TextInput::make('tax_amount_minor')->numeric()->disabled(),
                TextInput::make('fee_amount_minor')->numeric()->disabled(),
                TextInput::make('markup_amount_minor')->numeric()->disabled(),
                TextInput::make('discount_amount_minor')->numeric()->disabled(),
                TextInput::make('applied_promo_code')->disabled(),
                DateTimePicker::make('pricing_locked_at')->disabled(),
                TextInput::make('total_amount_minor')->numeric()->disabled(),
            ])->columns(3),
            Section::make('Lifecycle')->schema([
                DateTimePicker::make('offer_expires_at')->disabled(),
                DateTimePicker::make('confirmed_at')->disabled(),
                DateTimePicker::make('ticketed_at')->disabled(),
                DateTimePicker::make('ticketing_last_checked_at')->disabled(),
                DateTimePicker::make('failed_at')->disabled(),
                Textarea::make('failure_reason')->rows(3)->columnSpanFull(),
            ])->columns(3),
            Section::make('Pricing snapshot')->collapsed()->schema([
                KeyValue::make('pricing_snapshot')->disabled()->columnSpanFull(),
            ]),
            Section::make('Provider payload snapshot')->collapsed()->schema([
                KeyValue::make('provider_order_payload')->disabled()->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_reference')->label('Reference')->searchable()->sortable()->copyable(),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state): string => $state instanceof BookingStatus ? $state->label() : str((string) $state)->replace('_', ' ')->title()),
                TextColumn::make('customer_email')->searchable()->toggleable(),
                TextColumn::make('provider')->badge()->sortable(),
                TextColumn::make('pnr')->searchable()->placeholder('—')->copyable(),
                TextColumn::make('ticket_number')->searchable()->placeholder('—')->copyable()->toggleable(),
                TextColumn::make('applied_promo_code')->label('Promo')->badge()->placeholder('—')->toggleable(),
                TextColumn::make('total_amount_minor')->label('Total')->money(fn (Booking $record): string => $record->currency ?: 'USD', divideBy: 100)->sortable(),
                TextColumn::make('offer_expires_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('confirmed_at')->dateTime()->sortable()->toggleable(),
                TextColumn::make('failed_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(array_reduce(\App\Enums\BookingStatus::cases(), fn (array $carry, \App\Enums\BookingStatus $status) => $carry + [$status->value => $status->label()], [])),
                SelectFilter::make('provider')->options(array_reduce(\App\Enums\FlightProvider::cases(), fn (array $carry, \App\Enums\FlightProvider $provider) => $carry + [$provider->value => strtoupper($provider->value)], [])),
                Filter::make('payment_success_booking_not_confirmed')
                    ->label('Paid but not confirmed')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', [
                        BookingStatus::PaymentSucceeded->value,
                        BookingStatus::BookingConfirming->value,
                        BookingStatus::BookingFailed->value,
                    ])),
                Filter::make('ticketing_pending')
                    ->query(fn (Builder $query): Builder => $query->where('status', BookingStatus::TicketingPending->value)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('retry_finalization')
                    ->label('Retry finalization')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record): bool => in_array($record->status, [BookingStatus::PaymentSucceeded, BookingStatus::BookingFailed, BookingStatus::TicketingPending], true) && Gate::forUser(auth('admin')->user())->allows('retryFinalization', $record))
                    ->action(function (Booking $record): void {
                        Gate::forUser(auth('admin')->user())->authorize('retryFinalization', $record);
                        app(RetryBookingFinalizationAction::class)->handle($record, auth('admin')->id());
                    }),
                Action::make('record_ticket')
                    ->label('Record ticket')
                    ->icon('heroicon-o-check-badge')
                    ->form([
                        TextInput::make('ticket_number')->required()->maxLength(100),
                        TextInput::make('pnr')->maxLength(100),
                    ])
                    ->visible(fn (Booking $record): bool => in_array($record->status, [BookingStatus::BookingConfirmed, BookingStatus::TicketingPending], true) && Gate::forUser(auth('admin')->user())->allows('update', $record))
                    ->action(function (Booking $record, array $data): void {
                        Gate::forUser(auth('admin')->user())->authorize('update', $record);
                        app(RecordManualTicketIssuedAction::class)->handle($record, $data['ticket_number'], $data['pnr'] ?? null, auth('admin')->id());
                    }),
                Action::make('mark_refund_pending')
                    ->label('Mark refund pending')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record): bool => in_array($record->status, [BookingStatus::PaymentSucceeded, BookingStatus::BookingFailed, BookingStatus::TicketingPending, BookingStatus::Ticketed], true) && Gate::forUser(auth('admin')->user())->allows('markRefundPending', $record))
                    ->action(function (Booking $record): void {
                        Gate::forUser(auth('admin')->user())->authorize('markRefundPending', $record);
                        app(MarkBookingRefundPendingAction::class)->handle($record, auth('admin')->id());
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('15s')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PassengersRelationManager::class,
            RelationManagers\SegmentsRelationManager::class,
            RelationManagers\PriceBreakdownsRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
            RelationManagers\EventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'view' => Pages\ViewBooking::route('/{record}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
