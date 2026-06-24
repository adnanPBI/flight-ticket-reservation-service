<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminUserResource\Pages;
use App\Models\AdminUser;
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

class AdminUserResource extends Resource
{
    protected static ?string $model = AdminUser::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 80;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('AdminUser details')->schema([

                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('email')->email()->required()->maxLength(255),
                TextInput::make('password')->password()->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)->dehydrated(fn ($state) => filled($state))->maxLength(255),
                Select::make('role')->options(['super_admin' => 'Super admin', 'admin' => 'Admin', 'operations' => 'Operations', 'finance' => 'Finance', 'support' => 'Support'])->required()->disabled(fn (?AdminUser $record): bool => $record !== null && $record->id === auth('admin')->id()),
                Select::make('is_active')->options([1 => 'Active', 0 => 'Inactive'])->required()->disabled(fn (?AdminUser $record): bool => $record !== null && $record->id === auth('admin')->id()),
                DateTimePicker::make('last_login_at')->disabled(),

            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('role')->badge()->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('last_login_at')->dateTime()->sortable()->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),

            ])
            ->filters([

                SelectFilter::make('role')->options(['super_admin' => 'Super admin', 'admin' => 'Admin', 'operations' => 'Operations', 'finance' => 'Finance', 'support' => 'Support']),
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
            'index' => Pages\ListAdminUsers::route('/'),
            'create' => Pages\CreateAdminUser::route('/create'),
            'view' => Pages\ViewAdminUser::route('/{record}'),
            'edit' => Pages\EditAdminUser::route('/{record}/edit'),
        ];
    }
}
