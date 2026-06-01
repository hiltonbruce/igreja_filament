<?php

namespace App\Filament\Admin\Resources\Neighborhoods;

use App\Filament\Admin\Resources\Neighborhoods\Pages\CreateNeighborhood;
use App\Filament\Admin\Resources\Neighborhoods\Pages\EditNeighborhood;
use App\Filament\Admin\Resources\Neighborhoods\Pages\ListNeighborhoods;
use App\Filament\Admin\Resources\Neighborhoods\Pages\ViewNeighborhood;
use App\Filament\Admin\Resources\Neighborhoods\Schemas\NeighborhoodForm;
use App\Filament\Admin\Resources\Neighborhoods\Schemas\NeighborhoodInfolist;
use App\Filament\Admin\Resources\Neighborhoods\Tables\NeighborhoodsTable;
use App\Models\Neighborhood;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NeighborhoodResource extends Resource
{
    protected static ?string $model = Neighborhood::class;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?string $navigationLabel = null;

    public static function getModelLabel(): string
    {
        return __('custom.Neighborhood');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.Neighborhoods');
    }

    public static function getNavigationLabel(): string
    {
        return __('custom.Neighborhoods');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return NeighborhoodForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NeighborhoodInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NeighborhoodsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['city', 'state', 'country']);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNeighborhoods::route('/'),
            'create' => CreateNeighborhood::route('/create'),
            'view' => ViewNeighborhood::route('/{record}'),
            'edit' => EditNeighborhood::route('/{record}/edit'),
        ];
    }
}
