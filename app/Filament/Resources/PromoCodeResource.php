<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoCodeResource\Pages;
use App\Models\PromoCode;
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

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static string|\UnitEnum|null $navigationGroup = 'Commercial';
    protected static ?int $navigationSort = 51;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('PromoCode details')->schema([

                TextInput::make('code')->required()->maxLength(80),
                Textarea::make('description')->columnSpanFull(),
                Select::make('discount_type')->options(['fixed' => 'Fixed amount', 'percentage' => 'Percentage'])->required(),
                TextInput::make('value')->numeric()->required(),
                TextInput::make('currency')->maxLength(3),
                TextInput::make('max_discount_minor')->numeric(),
                TextInput::make('usage_limit')->numeric(),
                TextInput::make('used_count')->numeric()->disabled(),
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

                TextColumn::make('code')->searchable()->sortable()->badge(),
                TextColumn::make('discount_type')->badge(),
                TextColumn::make('value')->sortable(),
                TextColumn::make('currency')->toggleable(),
                TextColumn::make('usage_limit')->sortable(),
                TextColumn::make('used_count')->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('starts_at')->dateTime()->toggleable(),
                TextColumn::make('ends_at')->dateTime()->toggleable(),

            ])
            ->filters([

                SelectFilter::make('discount_type')->options(['fixed' => 'Fixed amount', 'percentage' => 'Percentage']),
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
            'index' => Pages\ListPromoCodes::route('/'),
            'create' => Pages\CreatePromoCode::route('/create'),
            'view' => Pages\ViewPromoCode::route('/{record}'),
            'edit' => Pages\EditPromoCode::route('/{record}/edit'),
        ];
    }
}
