<?php

namespace App\Filament\Secretary\Resources\Churches\Tables;

use App\Enums\ChurchLevel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChurchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('custom.Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level')
                    ->label(__('custom.Church level'))
                    ->badge()
                    ->formatStateUsing(fn (ChurchLevel $state): string => $state->getLabel())
                    ->color(fn (ChurchLevel $state): string => $state->getColor())
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label(__('custom.Parent church'))
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('region')
                    ->label(__('custom.Region'))
                    ->formatStateUsing(fn ($state): ?string => $state?->getLabel())
                    ->toggleable(),
                TextColumn::make('state.abbreviation')
                    ->label(__('custom.State'))
                    ->toggleable(),
                TextColumn::make('city.name')
                    ->label(__('custom.City'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label(__('custom.Phone'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('custom.Updated at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label(__('custom.Church level'))
                    ->options(ChurchLevel::class),
                SelectFilter::make('state_id')
                    ->label(__('custom.State'))
                    ->relationship('state', 'name')
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
