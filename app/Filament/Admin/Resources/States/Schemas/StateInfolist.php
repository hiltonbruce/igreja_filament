<?php

namespace App\Filament\Admin\Resources\States\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('abbreviation'),
                TextEntry::make('country_id')
                    ->numeric(),
                TextEntry::make('city_id')
                    ->numeric(),
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
