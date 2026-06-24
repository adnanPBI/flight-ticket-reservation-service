<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AirportResource\Pages;
use App\Models\Airport;
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

class AirportResource extends Resource
{
    protected static ?string $model = Airport::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';
    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Airport details')->schema([

                TextInput::make('iata_code')->required()->maxLength(3),
                TextInput::make('icao_code')->maxLength(4),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('city')->required()->maxLength(120),
                TextInput::make('country')->required()->maxLength(120),
                TextInput::make('timezone')->maxLength(80),
                TextInput::make('latitude')->numeric(),
                TextInput::make('longitude')->numeric(),
                Select::make('is_active')->options([1 => 'Active', 0 => 'Inactive'])->required(),

            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('iata_code')->searchable()->sortable()->badge(),
                TextColumn::make('icao_code')->searchable()->toggleable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('city')->searchable()->sortable(),
                TextColumn::make('country')->searchable()->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),

            ])
            ->filters([

                SelectFilter::make('is_active')->options([1 => 'Active', 0 => 'Inactive']),

            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAirports::route('/'),
            'create' => Pages\CreateAirport::route('/create'),
            'view' => Pages\ViewAirport::route('/{record}'),
            'edit' => Pages\EditAirport::route('/{record}/edit'),
        ];
    }
}
