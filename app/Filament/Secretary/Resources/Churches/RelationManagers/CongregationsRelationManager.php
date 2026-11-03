<?php

namespace App\Filament\Secretary\Resources\Churches\RelationManagers;

use App\Enums\ChurchLevel;
use App\Models\Church;
use App\Support\ChurchHierarchy;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CongregationsRelationManager extends RelationManager
{
    protected static string $relationship = 'congregations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('custom.Congregations');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('custom.Name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('address')
                    ->label(__('custom.Address'))
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label(__('custom.Phone'))
                    ->tel()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('custom.Name'))
                    ->searchable(),
                TextColumn::make('address')
                    ->label(__('custom.Address'))
                    ->placeholder('—'),
                TextColumn::make('phone')
                    ->label(__('custom.Phone'))
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        /** @var Church $owner */
                        $owner = $this->getOwnerRecord();

                        $data['level'] = ChurchLevel::Congregation->value;
                        $data['parent_id'] = $owner->id;
                        $data['city_id'] = $owner->city_id;
                        $data['state_id'] = $owner->state_id;
                        $data['country_id'] = $owner->country_id;
                        $data['neighborhood_id'] = $owner->neighborhood_id;
                        $data['timezone'] = $owner->timezone ?? 'America/Sao_Paulo';
                        $data['user_id'] = Auth::id();

                        try {
                            return ChurchHierarchy::validate($data);
                        } catch (ValidationException $exception) {
                            $this->setErrorBag($exception->errors(), $this->getMountedAction()?->getName() ?? 'mountedActionData');

                            throw $exception;
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(function (array $data, Church $record): array {
                        /** @var Church $owner */
                        $owner = $this->getOwnerRecord();

                        $data['level'] = ChurchLevel::Congregation->value;
                        $data['parent_id'] = $owner->id;
                        $data['city_id'] = $owner->city_id;
                        $data['state_id'] = $owner->state_id;
                        $data['country_id'] = $owner->country_id;
                        $data['user_id'] = $record->user_id;

                        try {
                            return ChurchHierarchy::validate($data, $record);
                        } catch (ValidationException $exception) {
                            $this->setErrorBag($exception->errors(), $this->getMountedAction()?->getName() ?? 'mountedActionData');

                            throw $exception;
                        }
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Church
            && $ownerRecord->level === ChurchLevel::Municipal;
    }
}
