<?php

namespace App\Filament\Resources\FlightProviderLogResource\Pages;

use App\Filament\Resources\FlightProviderLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFlightProviderLog extends ViewRecord
{
    protected static string $resource = FlightProviderLogResource::class;
    protected function getHeaderActions(): array { return [EditAction::make()]; }
}
