<?php

namespace App\Filament\Secretary\Resources\UnitTypes;

use App\Filament\Secretary\Resources\UnitTypes\Pages\CreateUnitType;
use App\Filament\Secretary\Resources\UnitTypes\Pages\EditUnitType;
use App\Filament\Secretary\Resources\UnitTypes\Pages\ListUnitTypes;
use App\Models\UnitType;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class UnitTypeResource extends Resource
{
    protected static ?string $model = UnitType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('custom.Positions and units');
    }

    public static function getModelLabel(): string
    {
        return __('custom.Unit type');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.Unit types');
    }

    public static function getNavigationLabel(): string
    {
        return __('custom.Unit types');
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
                Textarea::make('description')
                    ->label(__('custom.Description'))
                    ->maxLength(255)
                    ->columnSpanFull(),
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
                TextColumn::make('description')
                    ->label(__('custom.Description'))
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('units_count')
                    ->label(__('custom.Units'))
                    ->counts('units')
                    ->sortable(),
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
            'index' => ListUnitTypes::route('/'),
            'create' => CreateUnitType::route('/create'),
            'edit' => EditUnitType::route('/{record}/edit'),
        ];
    }
}
