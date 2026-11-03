<?php

namespace App\Filament\Secretary\Resources\Churches\Schemas;

use App\Enums\ChurchLevel;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChurchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('custom.Identification'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('custom.Name')),
                        TextEntry::make('level')
                            ->label(__('custom.Church level'))
                            ->badge()
                            ->formatStateUsing(fn (ChurchLevel $state): string => $state->getLabel())
                            ->color(fn (ChurchLevel $state): string => $state->getColor()),
                        TextEntry::make('parent.name')
                            ->label(__('custom.Parent church'))
                            ->placeholder('—'),
                        TextEntry::make('region')
                            ->label(__('custom.Region'))
                            ->formatStateUsing(fn ($state): ?string => $state?->getLabel())
                            ->placeholder('—'),
                        TextEntry::make('denomination')
                            ->label(__('custom.Denomination'))
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make(__('custom.Location'))
                    ->schema([
                        TextEntry::make('country.name')
                            ->label(__('custom.Country'))
                            ->placeholder('—'),
                        TextEntry::make('state.name')
                            ->label(__('custom.State'))
                            ->placeholder('—'),
                        TextEntry::make('city.name')
                            ->label(__('custom.City'))
                            ->placeholder('—'),
                        TextEntry::make('neighborhood.name')
                            ->label(__('custom.Neighborhood'))
                            ->placeholder('—'),
                        TextEntry::make('address')
                            ->label(__('custom.Address'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('postal_code')
                            ->label(__('custom.Zip Code'))
                            ->placeholder('—'),
                        TextEntry::make('timezone')
                            ->label(__('custom.Timezone'))
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make(__('custom.Contact'))
                    ->schema([
                        TextEntry::make('phone')
                            ->label(__('custom.Phone'))
                            ->placeholder('—'),
                        TextEntry::make('email')
                            ->label(__('custom.Email'))
                            ->placeholder('—'),
                        TextEntry::make('website')
                            ->label(__('custom.Website'))
                            ->placeholder('—'),
                    ])
                    ->columns(2),
            ]);
    }
}
