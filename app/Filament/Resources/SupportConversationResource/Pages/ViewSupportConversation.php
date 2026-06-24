<?php

namespace App\Filament\Resources\SupportConversationResource\Pages;

use App\Actions\Chat\SendChatMessageAction;
use App\Filament\Resources\SupportConversationResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewSupportConversation extends ViewRecord
{
    protected static string $resource = SupportConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('Reply to customer')
                ->icon('heroicon-o-paper-airplane')
                ->schema([
                    Textarea::make('body')
                        ->label('Message')
                        ->required()
                        ->maxLength(3000)
                        ->rows(5),
                ])
                ->action(function (array $data): void {
                    app(SendChatMessageAction::class)->execute(
                        conversation: $this->record,
                        body: $data['body'],
                        senderType: 'admin',
                        senderId: auth('admin')->id(),
                        metadata: ['source' => 'filament_support_conversation'],
                    );
                }),
            EditAction::make(),
        ];
    }
}
