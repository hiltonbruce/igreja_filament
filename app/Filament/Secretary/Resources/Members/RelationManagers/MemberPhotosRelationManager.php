<?php

namespace App\Filament\Secretary\Resources\Members\RelationManagers;

use App\Models\MemberPhoto;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MemberPhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'memberPhotos';

    protected static bool $shouldSkipAuthorization = true;

    public function isReadOnly(): bool
    {
        return true;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('custom.Photo history');
    }

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('supersededBy'))
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label(__('custom.Photo'))
                    ->disk(fn (MemberPhoto $record): string => $record->disk)
                    ->visibility('private')
                    ->height(72),
                Tables\Columns\TextColumn::make('path')
                    ->label(__('custom.File path'))
                    ->limit(48)
                    ->tooltip(fn (MemberPhoto $record): string => $record->path),
                Tables\Columns\TextColumn::make('supersededBy.name')
                    ->label(__('custom.Replaced by'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('custom.Replaced at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
