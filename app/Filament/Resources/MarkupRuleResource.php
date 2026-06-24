<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarkupRuleResource\Pages;
use App\Models\MarkupRule;
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

class MarkupRuleResource extends Resource
{
    protected static ?string $model = MarkupRule::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static string|\UnitEnum|null $navigationGroup = 'Commercial';
    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('MarkupRule details')->schema([

                TextInput::make('name')->required()->maxLength(255),
                Select::make('scope')->options(['global' => 'Global', 'route' => 'Route', 'airline' => 'Airline', 'cabin' => 'Cabin'])->required(),
                KeyValue::make('match_rules')->columnSpanFull(),
                Select::make('calculation_type')->options(['fixed' => 'Fixed amount', 'percentage' => 'Percentage'])->required(),
                TextInput::make('value')->numeric()->required(),
                TextInput::make('currency')->maxLength(3),
                TextInput::make('priority')->numeric()->required(),
                Select::make('is_active')->options([1 => 'Active', 0 => 'Inactive'])->required(),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),

            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('scope')->badge()->sortable(),
                TextColumn::make('calculation_type')->badge(),
                TextColumn::make('value')->sortable(),
                TextColumn::make('currency')->toggleable(),
                TextColumn::make('priority')->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('starts_at')->dateTime()->toggleable(),
                TextColumn::make('ends_at')->dateTime()->toggleable(),

            ])
            ->filters([

                SelectFilter::make('scope')->options(['global' => 'Global', 'route' => 'Route', 'airline' => 'Airline', 'cabin' => 'Cabin']),
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
            'index' => Pages\ListMarkupRules::route('/'),
            'create' => Pages\CreateMarkupRule::route('/create'),
            'view' => Pages\ViewMarkupRule::route('/{record}'),
            'edit' => Pages\EditMarkupRule::route('/{record}/edit'),
        ];
    }
}
