<?php

namespace App\Filament\Admin\Resources\Neighborhoods\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NeighborhoodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('custom.Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city.name')
                    ->label(__('custom.City'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('state.name')
                    ->label(__('custom.State'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country.name')
                    ->label(__('custom.Country'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label(__('custom.Country'))
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('state_id')
                    ->label(__('custom.State'))
                    ->relationship('state', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('city_id')
                    ->label(__('custom.City'))
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
