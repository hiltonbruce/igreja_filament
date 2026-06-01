<?php

namespace App\Filament\Admin\Resources\Neighborhoods\Schemas;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class NeighborhoodForm
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
                    ->afterStateUpdated(function (callable $set): void {
                        $set('state_id', null);
                        $set('city_id', null);
                    }),
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
                    ->live()
                    ->disabled(fn (Get $get): bool => ! $get('country_id'))
                    ->afterStateUpdated(fn (callable $set) => $set('city_id', null)),
                Select::make('city_id')
                    ->label(__('custom.City'))
                    ->options(fn (Get $get): array => $get('state_id')
                        ? City::query()
                            ->where('state_id', $get('state_id'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                        : []
                    )
                    ->searchable()
                    ->required()
                    ->disabled(fn (Get $get): bool => ! $get('state_id')),
                TextInput::make('user_id')
                    ->numeric()
                    ->hiddenOn(['create', 'edit']),
            ]);
    }
}
