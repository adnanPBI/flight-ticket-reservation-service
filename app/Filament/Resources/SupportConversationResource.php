<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportConversationResource\Pages;
use App\Filament\Resources\SupportConversationResource\RelationManagers;
use App\Models\ChatConversation;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportConversationResource extends Resource
{
    protected static ?string $model = ChatConversation::class;
    protected static ?string $modelLabel = 'Support conversation';
    protected static ?string $pluralModelLabel = 'Support conversations';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string|\UnitEnum|null $navigationGroup = 'Support';
    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Conversation')->schema([
                TextInput::make('visitor_token')->disabled(),
                TextInput::make('user.email')->label('User email')->disabled(),
                TextInput::make('booking.booking_reference')->label('Booking')->disabled(),
                Select::make('status')->options(['open' => 'Open', 'pending' => 'Pending', 'closed' => 'Closed'])->required(),
                DateTimePicker::make('last_message_at')->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('user.email')->label('Customer')->searchable()->placeholder('Guest'),
                TextColumn::make('booking.booking_reference')->label('Booking')->searchable()->placeholder('—'),
                TextColumn::make('visitor_token')->limit(16)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('messages_count')->counts('messages')->label('Messages')->sortable(),
                TextColumn::make('last_message_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['open' => 'Open', 'pending' => 'Pending', 'closed' => 'Closed']),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->defaultSort('last_message_at', 'desc')
            ->poll('10s');
    }

    public static function getRelations(): array
    {
        return [RelationManagers\MessagesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportConversations::route('/'),
            'view' => Pages\ViewSupportConversation::route('/{record}'),
            'edit' => Pages\EditSupportConversation::route('/{record}/edit'),
        ];
    }
}
