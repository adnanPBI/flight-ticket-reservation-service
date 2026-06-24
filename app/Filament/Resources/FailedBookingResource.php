<?php

namespace App\Filament\Resources;

use App\Actions\Admin\MarkBookingRefundPendingAction;
use App\Actions\Admin\RetryBookingFinalizationAction;
use App\Enums\BookingStatus;
use App\Filament\Resources\FailedBookingResource\Pages;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FailedBookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $modelLabel = 'Failed booking';
    protected static ?string $pluralModelLabel = 'Failed bookings';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static string|\UnitEnum|null $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 11;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('status', [
            BookingStatus::BookingFailed->value,
            BookingStatus::RefundPending->value,
            BookingStatus::TicketingPending->value,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Failure details')->schema([
                \Filament\Forms\Components\TextInput::make('booking_reference')->disabled(),
                \Filament\Forms\Components\TextInput::make('status')->disabled(),
                \Filament\Forms\Components\TextInput::make('customer_email')->disabled(),
                \Filament\Forms\Components\Textarea::make('failure_reason')->disabled()->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_reference')->searchable()->copyable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('customer_email')->searchable(),
                TextColumn::make('pnr')->placeholder('—'),
                TextColumn::make('total_amount_minor')->money(fn (Booking $record): string => $record->currency ?: 'USD', divideBy: 100),
                TextColumn::make('failure_reason')->limit(60)->wrap(),
                TextColumn::make('failed_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('retry')->requiresConfirmation()->action(fn (Booking $record) => app(RetryBookingFinalizationAction::class)->handle($record, auth('admin')->id())),
                Action::make('refund_pending')->label('Refund pending')->color('warning')->requiresConfirmation()->action(fn (Booking $record) => app(MarkBookingRefundPendingAction::class)->handle($record, auth('admin')->id())),
            ])
            ->defaultSort('failed_at', 'desc')
            ->poll('15s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFailedBookings::route('/'),
            'view' => Pages\ViewFailedBooking::route('/{record}'),
        ];
    }
}
