<?php

namespace App\Filament\Admin\Resources\Countries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('iso_code')
                    ->required(),
                TextInput::make('phone_code')
                    ->tel(),
                TextInput::make('currency'),
                TextInput::make('user_id')
                    ->numeric(),
            ]);
    }
}
