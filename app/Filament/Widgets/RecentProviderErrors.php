<?php

namespace App\Filament\Widgets;

use App\Models\FlightProviderLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentProviderErrors extends TableWidget
{
    protected static ?string $heading = 'Recent provider errors';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => FlightProviderLog::query()->whereNotNull('error_message')->latest()->limit(10))
            ->columns([
                TextColumn::make('provider')->badge(),
                TextColumn::make('endpoint')->limit(48),
                TextColumn::make('status_code'),
                TextColumn::make('error_message')->wrap()->limit(90),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->paginated(false);
    }
}
