<?php

namespace App\Filament\Admin\Resources\Countries;

use App\Filament\Admin\Resources\Countries\Pages\CreateCountry;
use App\Filament\Admin\Resources\Countries\Pages\EditCountry;
use App\Filament\Admin\Resources\Countries\Pages\ListCountries;
use App\Filament\Admin\Resources\Countries\Pages\ViewCountry;
use App\Filament\Admin\Resources\Countries\Schemas\CountryForm;
use App\Filament\Admin\Resources\Countries\Schemas\CountryInfolist;
use App\Filament\Admin\Resources\Countries\Tables\CountriesTable;
use App\Models\Country;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?string $navigationLabel = null;

    public static function getModelLabel(): string
    {
        return __('custom.Country');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.Countries');
    }

    public static function getNavigationLabel(): string
    {
        return __('custom.Countries');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Country';

    public static function form(Schema $schema): Schema
    {
        return CountryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CountryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CountriesTable::configure($table);
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
            'index' => ListCountries::route('/'),
            'create' => CreateCountry::route('/create'),
            'view' => ViewCountry::route('/{record}'),
            'edit' => EditCountry::route('/{record}/edit'),
        ];
    }
}
