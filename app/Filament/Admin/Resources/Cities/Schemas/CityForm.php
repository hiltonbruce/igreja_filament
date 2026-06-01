<?php

namespace App\Filament\Admin\Resources\Cities\Schemas;

use App\Models\Country;
use App\Models\State;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('custom.Name'))
                    ->required()
                    ->maxLength(255),
                Select::make('country_id')
                    ->label(__('custom.Country'))
                    ->options(Country::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required()
                    ->default(55)
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('state_id', null)),
                Select::make('state_id')
                    ->label(__('custom.State'))
                    ->options(fn (Get $get): array => $get('country_id')
                        ? State::query()
                            ->where('country_id', $get('country_id'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                        : []
                    )
                    ->searchable()
                    ->required()
                    ->disabled(fn (Get $get): bool => ! $get('country_id')),
                Toggle::make('capital_city')
                    ->label(__('custom.Capital City'))
                    ->default(false),
                TextInput::make('ibge_code')
                    ->label(__('custom.IBGE Code'))
                    ->required()
                    ->length(7)
                    ->unique(ignoreRecord: true),
                TextInput::make('latitude')
                    ->maxLength(10),
                TextInput::make('longitude')
                    ->maxLength(10),
                TextInput::make('timezone')
                    ->default('America/Sao_Paulo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('area_code')
                    ->label(__('custom.Area Code'))
                    ->maxLength(5),
                TextInput::make('user_id')
                    ->numeric()
                    ->hiddenOn(['create', 'edit']),
            ]);
    }
}
