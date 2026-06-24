<?php

namespace App\Filament\Resources\MarkupRuleResource\Pages;

use App\Filament\Resources\MarkupRuleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMarkupRule extends ViewRecord
{
    protected static string $resource = MarkupRuleResource::class;
    protected function getHeaderActions(): array { return [EditAction::make()]; }
}
