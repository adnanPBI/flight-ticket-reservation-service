<?php

namespace App\Filament\Resources\FailedBookingResource\Pages;

use App\Filament\Resources\FailedBookingResource;
use Filament\Resources\Pages\ListRecords;

class ListFailedBookings extends ListRecords
{
    protected static string $resource = FailedBookingResource::class;
}
