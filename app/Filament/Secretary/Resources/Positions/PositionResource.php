<?php

namespace App\Filament\Secretary\Resources\Positions;

use App\Enums\PositionScope;
use App\Filament\Secretary\Resources\Positions\Pages\CreatePosition;
use App\Filament\Secretary\Resources\Positions\Pages\EditPosition;
use App\Filament\Secretary\Resources\Positions\Pages\ListPositions;
use App\Models\Position;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('custom.Positions and units');
    }

    public static function getModelLabel(): string
    {
        return __('custom.Position');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.Positions');
    }

    public static function getNavigationLabel(): string
    {
        return __('custom.Positions');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('custom.Name'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('scope')
                    ->label(__('custom.Scope'))
                    ->options(PositionScope::class)
                    ->default(PositionScope::Both)
                    ->required(),
                Textarea::make('description')
                    ->label(__('custom.Description'))
                    ->maxLength(255)
                    ->columnSpanFull(),
                Toggle::make('supports_ordinal')
                    ->label(__('custom.Supports ordinal'))
                    ->helperText(__('custom.Allows setting 1st, 2nd, 3rd... for this position.')),
                Toggle::make('is_active')
                    ->label(__('custom.Active'))
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('custom.Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scope')
                    ->label(__('custom.Scope'))
                    ->badge()
                    ->formatStateUsing(fn (PositionScope $state): string => $state->getLabel())
                    ->sortable(),
                IconColumn::make('supports_ordinal')
                    ->label(__('custom.Supports ordinal'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('custom.Active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('custom.Updated at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('scope')
                    ->label(__('custom.Scope'))
                    ->options(PositionScope::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPositions::route('/'),
            'create' => CreatePosition::route('/create'),
            'edit' => EditPosition::route('/{record}/edit'),
        ];
    }
}
