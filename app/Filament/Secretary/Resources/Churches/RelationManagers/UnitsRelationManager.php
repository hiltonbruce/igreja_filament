<?php

namespace App\Filament\Secretary\Resources\Churches\RelationManagers;

use App\Models\Church;
use App\Models\Unit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('custom.Units');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('unit_type_id')
                    ->label(__('custom.Unit type'))
                    ->relationship('unitType', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('parent_id')
                    ->label(__('custom.Parent unit'))
                    ->options(fn (?Unit $record): array => $this->parentUnitOptions($record))
                    ->searchable()
                    ->placeholder('—'),
                TextInput::make('name')
                    ->label(__('custom.Unit name'))
                    ->maxLength(255)
                    ->helperText(__('custom.Leave blank to use the unit type name.')),
                Textarea::make('description')
                    ->label(__('custom.Description'))
                    ->maxLength(255)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label(__('custom.Active'))
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('custom.Unit'))
                    ->state(fn (Unit $record): string => $record->displayName())
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unitType.name')
                    ->label(__('custom.Unit type'))
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label(__('custom.Parent unit'))
                    ->state(fn (Unit $record): ?string => $record->parent?->displayName())
                    ->placeholder('—'),
                IconColumn::make('is_active')
                    ->label(__('custom.Active'))
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->injectOwnerData($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->injectOwnerData($data)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function injectOwnerData(array $data): array
    {
        /** @var Church $owner */
        $owner = $this->getOwnerRecord();

        $data['church_id'] = $owner->id;
        $data['user_id'] ??= Auth::id();

        return $data;
    }

    /**
     * @return array<int, string>
     */
    protected function parentUnitOptions(?Unit $record): array
    {
        /** @var Church $owner */
        $owner = $this->getOwnerRecord();

        return Unit::query()
            ->where('church_id', $owner->id)
            ->when($record, fn (Builder $query): Builder => $query->whereKeyNot($record->getKey()))
            ->with('unitType')
            ->get()
            ->mapWithKeys(fn (Unit $unit): array => [$unit->id => $unit->displayName()])
            ->all();
    }
}
