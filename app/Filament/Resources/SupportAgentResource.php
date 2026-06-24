<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportAgentResource\Pages;
use App\Models\SupportAgent;
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

class SupportAgentResource extends Resource
{
    protected static ?string $model = SupportAgent::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    protected static string|\UnitEnum|null $navigationGroup = 'Support';
    protected static ?int $navigationSort = 61;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('SupportAgent details')->schema([

                Select::make('admin_user_id')->relationship('adminUser', 'name')->searchable()->preload()->required(),
                TextInput::make('display_name')->required()->maxLength(255),
                Select::make('is_online')->options([1 => 'Online', 0 => 'Offline'])->required(),
                DateTimePicker::make('last_seen_at'),

            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('display_name')->searchable()->sortable(),
                TextColumn::make('adminUser.email')->label('Email')->searchable(),
                IconColumn::make('is_online')->boolean()->sortable(),
                TextColumn::make('last_seen_at')->dateTime()->sortable()->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),

            ])
            ->filters([

                SelectFilter::make('is_online')->options([1 => 'Online', 0 => 'Offline']),

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
            'index' => Pages\ListSupportAgents::route('/'),
            'create' => Pages\CreateSupportAgent::route('/create'),
            'view' => Pages\ViewSupportAgent::route('/{record}'),
            'edit' => Pages\EditSupportAgent::route('/{record}/edit'),
        ];
    }
}
