<?php

namespace App\Filament\Secretary\Resources\Churches\RelationManagers;

use App\Models\Appointment;
use App\Models\Church;
use App\Models\Member;
use App\Models\Position;
use App\Models\Unit;
use App\Support\AppointmentRules;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('custom.Appointments');
    }

    public function form(Schema $schema): Schema
    {
        $ownerIsMember = $this->getOwnerRecord() instanceof Member;

        return $schema
            ->components([
                Select::make('member_id')
                    ->label(__('custom.Member'))
                    ->relationship('member', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(! $ownerIsMember),
                Select::make('church_id')
                    ->label(__('custom.Church'))
                    ->relationship('church', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set): mixed => $set('unit_id', null))
                    ->visible($ownerIsMember),
                Select::make('position_id')
                    ->label(__('custom.Position'))
                    ->relationship('position', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get): void {
                        if (! $this->positionSupportsOrdinal($get('position_id'))) {
                            $set('ordinal', null);
                        }

                        if (! $this->positionAllowsUnit($get('position_id'))) {
                            $set('unit_id', null);
                        }
                    }),
                Select::make('unit_id')
                    ->label(__('custom.Unit'))
                    ->options(fn (Get $get): array => $this->unitOptions($get))
                    ->searchable()
                    ->live()
                    ->required(fn (Get $get): bool => $this->positionRequiresUnit($get('position_id')))
                    ->visible(fn (Get $get): bool => $this->positionAllowsUnit($get('position_id'))),
                TextInput::make('ordinal')
                    ->label(__('custom.Ordinal'))
                    ->integer()
                    ->minValue(1)
                    ->required(fn (Get $get): bool => $this->positionSupportsOrdinal($get('position_id')))
                    ->visible(fn (Get $get): bool => $this->positionSupportsOrdinal($get('position_id'))),
                DatePicker::make('started_at')
                    ->label(__('custom.Start date'))
                    ->default(now()->toDateString()),
                DatePicker::make('ended_at')
                    ->label(__('custom.End date')),
                Toggle::make('is_active')
                    ->label(__('custom.Active'))
                    ->default(true),
                TextInput::make('notes')
                    ->label(__('custom.Notes'))
                    ->maxLength(255)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        $ownerIsMember = $this->getOwnerRecord() instanceof Member;

        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('is_active', 'desc')
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('custom.Member'))
                    ->searchable()
                    ->sortable()
                    ->hidden($ownerIsMember),
                TextColumn::make('position.name')
                    ->label(__('custom.Position'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('church.name')
                    ->label(__('custom.Church'))
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('unit')
                    ->label(__('custom.Unit'))
                    ->state(fn (Appointment $record): ?string => $record->unit?->displayName())
                    ->placeholder('—'),
                TextColumn::make('ordinal')
                    ->label(__('custom.Ordinal'))
                    ->placeholder('—'),
                IconColumn::make('is_active')
                    ->label(__('custom.Active'))
                    ->boolean(),
                TextColumn::make('started_at')
                    ->label(__('custom.Start date'))
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->prepareData($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data, Appointment $record): array => $this->prepareData($data, $record)),
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
    protected function prepareData(array $data, ?Appointment $record = null): array
    {
        $owner = $this->getOwnerRecord();

        if ($owner instanceof Member) {
            $data['member_id'] = $owner->id;
        }

        if ($owner instanceof Church) {
            $data['church_id'] = $owner->id;
        }

        $data['user_id'] = $record?->user_id ?? Auth::id();

        try {
            return AppointmentRules::validate($data, $record);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->errors(), $this->getMountedAction()?->getName() ?? 'mountedActionData');

            throw $exception;
        }
    }

    /**
     * @return array<int, string>
     */
    protected function unitOptions(Get $get): array
    {
        $owner = $this->getOwnerRecord();
        $churchId = $owner instanceof Church ? $owner->id : $get('church_id');

        if (blank($churchId)) {
            return [];
        }

        return Unit::query()
            ->where('church_id', $churchId)
            ->where('is_active', true)
            ->with('unitType')
            ->get()
            ->mapWithKeys(fn (Unit $unit): array => [$unit->id => $unit->displayName()])
            ->all();
    }

    protected function positionSupportsOrdinal(mixed $positionId): bool
    {
        return (bool) ($this->resolvePosition($positionId)?->supports_ordinal);
    }

    protected function positionAllowsUnit(mixed $positionId): bool
    {
        return $this->resolvePosition($positionId)?->scope?->allowsUnit() ?? true;
    }

    protected function positionRequiresUnit(mixed $positionId): bool
    {
        $scope = $this->resolvePosition($positionId)?->scope;

        return $scope !== null && $scope->allowsUnit() && ! $scope->allowsChurch();
    }

    protected function resolvePosition(mixed $positionId): ?Position
    {
        if (blank($positionId)) {
            return null;
        }

        return Position::query()->find($positionId);
    }
}
