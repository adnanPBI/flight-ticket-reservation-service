<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
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

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 90;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('AuditLog details')->schema([

                TextInput::make('actor_user_id')->disabled(),
                TextInput::make('auditable_type')->disabled(),
                TextInput::make('auditable_id')->disabled(),
                TextInput::make('action')->disabled(),
                TextInput::make('ip_address')->disabled(),
                Textarea::make('user_agent')->disabled()->columnSpanFull(),
                KeyValue::make('old_values')->disabled()->columnSpanFull(),
                KeyValue::make('new_values')->disabled()->columnSpanFull(),
                KeyValue::make('metadata')->disabled()->columnSpanFull(),

            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('action')->searchable()->sortable(),
                TextColumn::make('auditable_type')->searchable()->limit(36)->toggleable(),
                TextColumn::make('auditable_id')->sortable()->toggleable(),
                TextColumn::make('actor_user_id')->sortable()->toggleable(),
                TextColumn::make('ip_address')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),

            ])
            ->filters([


            ])
            ->headerActions([

            ])
            ->recordActions( [ViewAction::make()] )
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),

            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
