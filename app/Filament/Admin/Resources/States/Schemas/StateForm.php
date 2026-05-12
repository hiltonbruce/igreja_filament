<?php

namespace App\Filament\Admin\Resources\States\Schemas;

use App\Models\Country;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('abbreviation')
                    ->maxLength(2)
                    ->required(),
                Select::make('country_id')
                    ->options(Country::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required()
                    ->default(55),
                TextInput::make('city_id')
                    ->numeric(),
                TextInput::make('user_id')
                    ->numeric(),
            ]);
    }
}
