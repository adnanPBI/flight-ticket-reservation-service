<?php

namespace App\Filament\Resources\SupportAgentResource\Pages;

use App\Filament\Resources\SupportAgentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSupportAgent extends ViewRecord
{
    protected static string $resource = SupportAgentResource::class;
    protected function getHeaderActions(): array { return [EditAction::make()]; }
}
