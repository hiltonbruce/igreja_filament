<?php

namespace App\Filament\Admin\Resources\Neighborhoods\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class NeighborhoodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('custom.Name')),
                TextEntry::make('city.name')
                    ->label(__('custom.City')),
                TextEntry::make('state.name')
                    ->label(__('custom.State')),
                TextEntry::make('country.name')
                    ->label(__('custom.Country')),
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
