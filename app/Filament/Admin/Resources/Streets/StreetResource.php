<?php

namespace App\Filament\Admin\Resources\Streets;

use App\Filament\Admin\Resources\Streets\Pages\CreateStreet;
use App\Filament\Admin\Resources\Streets\Pages\EditStreet;
use App\Filament\Admin\Resources\Streets\Pages\ListStreets;
use App\Filament\Admin\Resources\Streets\Pages\ViewStreet;
use App\Filament\Admin\Resources\Streets\Schemas\StreetForm;
use App\Filament\Admin\Resources\Streets\Schemas\StreetInfolist;
use App\Filament\Admin\Resources\Streets\Tables\StreetsTable;
use App\Models\Street;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StreetResource extends Resource
{
    protected static ?string $model = Street::class;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?string $navigationLabel = null;

    public static function getModelLabel(): string
    {
        return __('custom.Street');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.Streets');
    }

    public static function getNavigationLabel(): string
    {
        return __('custom.Streets');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return StreetForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StreetInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StreetsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['neighborhood', 'city', 'state', 'country']);
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
            'index' => ListStreets::route('/'),
            'create' => CreateStreet::route('/create'),
            'view' => ViewStreet::route('/{record}'),
            'edit' => EditStreet::route('/{record}/edit'),
        ];
    }
}
