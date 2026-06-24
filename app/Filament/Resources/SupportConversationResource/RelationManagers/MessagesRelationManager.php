<?php

namespace App\Filament\Resources\SupportConversationResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('sender_type')->options(['customer' => 'Customer', 'admin' => 'Admin', 'system' => 'System'])->required(),
            Textarea::make('body')->required()->rows(4)->columnSpanFull(),
            KeyValue::make('metadata')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sender_type')->badge(),
                TextColumn::make('sender_id')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('body')->wrap()->searchable(),
                TextColumn::make('read_at')->dateTime()->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->headerActions([CreateAction::make()])
            ->defaultSort('created_at');
    }
}
