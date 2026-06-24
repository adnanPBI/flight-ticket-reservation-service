<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\ChatConversation;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OtaStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $todayBookings = Booking::query()->whereDate('created_at', today())->count();
        $todayPaidMinor = Payment::query()
            ->where('status', PaymentStatus::Succeeded->value)
            ->whereDate('paid_at', today())
            ->sum('amount_minor');

        return [
            Stat::make('Today bookings', (string) $todayBookings)
                ->description('Bookings created today'),
            Stat::make('Today paid volume', sprintf('USD %0.2f', $todayPaidMinor / 100))
                ->description('Succeeded payments today'),
            Stat::make('Failed bookings', (string) Booking::query()->where('status', BookingStatus::BookingFailed->value)->count())
                ->description('Need operations review'),
            Stat::make('Open chats', (string) ChatConversation::query()->where('status', 'open')->count())
                ->description('Support conversations'),
        ];
    }
}
