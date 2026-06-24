<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlightProviderLogResource\Pages;
use App\Models\FlightProviderLog;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FlightProviderLogResource extends Resource
{
    protected static ?string $model = FlightProviderLog::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-server-stack';
    protected static string|\UnitEnum|null $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('FlightProviderLog details')->schema([

                TextInput::make('provider')->disabled(),
                TextInput::make('direction')->disabled(),
                TextInput::make('endpoint')->disabled(),
                TextInput::make('method')->disabled(),
                TextInput::make('status_code')->disabled(),
                TextInput::make('correlation_id')->disabled(),
                TextInput::make('duration_ms')->disabled(),
                Textarea::make('error_message')->disabled()->columnSpanFull(),
                KeyValue::make('request_payload')->disabled()->columnSpanFull(),
                KeyValue::make('response_payload')->disabled()->columnSpanFull(),

            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('provider')->badge()->sortable(),
                TextColumn::make('direction')->badge()->sortable(),
                TextColumn::make('method')->badge(),
                TextColumn::make('endpoint')->searchable()->limit(48),
                TextColumn::make('status_code')->sortable(),
                TextColumn::make('duration_ms')->sortable(),
                TextColumn::make('booking.booking_reference')->label('Booking')->searchable()->toggleable(),
                TextColumn::make('flight_search.search_reference')->label('Search')->searchable()->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),

            ])
            ->filters([

                SelectFilter::make('provider')->options(array_reduce(\App\Enums\FlightProvider::cases(), fn (array $carry, \App\Enums\FlightProvider $provider) => $carry + [$provider->value => strtoupper($provider->value)], [])),
                SelectFilter::make('direction')->options(['request' => 'Request', 'response' => 'Response', 'error' => 'Error']),

            ])
            ->headerActions([

            ])
            ->recordActions( [ViewAction::make()] )
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlightProviderLogs::route('/'),

            'view' => Pages\ViewFlightProviderLog::route('/{record}'),
            'edit' => Pages\EditFlightProviderLog::route('/{record}/edit'),
        ];
    }
}
