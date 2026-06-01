<?php

namespace App\Filament\Admin\Resources\Cities\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('custom.Name')),
                TextEntry::make('state.name')
                    ->label(__('custom.State')),
                TextEntry::make('country.name')
                    ->label(__('custom.Country')),
                IconEntry::make('capital_city')
                    ->label(__('custom.Capital City'))
                    ->boolean(),
                TextEntry::make('ibge_code')
                    ->label(__('custom.IBGE Code')),
                TextEntry::make('latitude'),
                TextEntry::make('longitude'),
                TextEntry::make('timezone'),
                TextEntry::make('area_code')
                    ->label(__('custom.Area Code')),
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
