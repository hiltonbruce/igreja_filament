<?php

namespace App\Filament\Admin\Resources\Cities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('custom.Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('state.name')
                    ->label(__('custom.State'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('country.name')
                    ->label(__('custom.Country'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('capital_city')
                    ->label(__('custom.Capital City'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('ibge_code')
                    ->label(__('custom.IBGE Code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('area_code')
                    ->label(__('custom.Area Code'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('timezone')
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
                TernaryFilter::make('capital_city')
                    ->label(__('custom.Capital City')),
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
