<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SecurityEventResource\Pages;
use App\Models\SecurityEvent;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SecurityEventResource extends Resource
{
    protected static ?string $model = SecurityEvent::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 91;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Security event')->schema([
                TextInput::make('event_type')->disabled(),
                TextInput::make('severity')->disabled(),
                TextInput::make('user_id')->disabled(),
                TextInput::make('admin_user_id')->disabled(),
                TextInput::make('subject_type')->disabled(),
                TextInput::make('subject_id')->disabled(),
                TextInput::make('ip_address')->disabled(),
                Textarea::make('user_agent')->disabled()->columnSpanFull(),
                KeyValue::make('metadata')->disabled()->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_type')->searchable()->sortable(),
                TextColumn::make('severity')->badge()->sortable(),
                TextColumn::make('subject_type')->limit(32)->toggleable(),
                TextColumn::make('subject_id')->sortable()->toggleable(),
                TextColumn::make('ip_address')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('severity')->options([
                    'info' => 'Info',
                    'warning' => 'Warning',
                    'critical' => 'Critical',
                ]),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSecurityEvents::route('/'),
            'view' => Pages\ViewSecurityEvent::route('/{record}'),
        ];
    }
}
