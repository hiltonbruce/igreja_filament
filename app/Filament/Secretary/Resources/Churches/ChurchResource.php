<?php

namespace App\Filament\Secretary\Resources\Churches;

use App\Filament\Secretary\Resources\Churches\Pages\CreateChurch;
use App\Filament\Secretary\Resources\Churches\Pages\EditChurch;
use App\Filament\Secretary\Resources\Churches\Pages\ListChurches;
use App\Filament\Secretary\Resources\Churches\Pages\ViewChurch;
use App\Filament\Secretary\Resources\Churches\RelationManagers\CongregationsRelationManager;
use App\Filament\Secretary\Resources\Churches\Schemas\ChurchForm;
use App\Filament\Secretary\Resources\Churches\Schemas\ChurchInfolist;
use App\Filament\Secretary\Resources\Churches\Tables\ChurchesTable;
use App\Models\Church;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ChurchResource extends Resource
{
    protected static ?string $model = Church::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('custom.Registrations');
    }

    public static function getModelLabel(): string
    {
        return __('custom.Church');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.Churches');
    }

    public static function getNavigationLabel(): string
    {
        return __('custom.Churches');
    }

    public static function form(Schema $schema): Schema
    {
        return ChurchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ChurchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChurchesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['parent', 'state', 'city', 'country']);
    }

    public static function getRelations(): array
    {
        return [
            CongregationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChurches::route('/'),
            'create' => CreateChurch::route('/create'),
            'view' => ViewChurch::route('/{record}'),
            'edit' => EditChurch::route('/{record}/edit'),
        ];
    }
}
