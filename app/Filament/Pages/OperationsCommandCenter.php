<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OtaStatsOverview;
use App\Filament\Widgets\PendingTicketingTable;
use App\Filament\Widgets\RecentProviderErrors;
use Filament\Pages\Page;

class OperationsCommandCenter extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static string|\UnitEnum|null $navigationGroup = 'Operations';
    protected static ?string $navigationLabel = 'Command center';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.operations-command-center';

    protected function getHeaderWidgets(): array
    {
        return [
            OtaStatsOverview::class,
            PendingTicketingTable::class,
            RecentProviderErrors::class,
        ];
    }
}
