<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingTicketingTable extends TableWidget
{
    protected static ?string $heading = 'Pending ticketing';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Booking::query()->where('status', BookingStatus::TicketingPending->value)->latest()->limit(10))
            ->columns([
                TextColumn::make('booking_reference')->searchable()->copyable(),
                TextColumn::make('customer_email')->searchable(),
                TextColumn::make('provider')->badge(),
                TextColumn::make('provider_order_id')->copyable()->limit(28),
                TextColumn::make('total_amount_minor')->money(fn (Booking $record): string => $record->currency ?: 'USD', divideBy: 100),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->paginated(false)
            ->poll('15s');
    }
}
