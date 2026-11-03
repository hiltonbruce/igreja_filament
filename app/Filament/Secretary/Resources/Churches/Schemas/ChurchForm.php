<?php

namespace App\Filament\Secretary\Resources\Churches\Schemas;

use App\Actions\ResolveAddressByZipCode;
use App\Actions\ResolveForeignLocation;
use App\Enums\BrazilRegion;
use App\Enums\ChurchLevel;
use App\Models\Church;
use App\Models\City;
use App\Models\Country;
use App\Models\Neighborhood;
use App\Models\State;
use App\Support\ChurchHierarchy;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ChurchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('custom.Identification'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('custom.Name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('level')
                            ->label(__('custom.Church level'))
                            ->options(ChurchLevel::class)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('parent_id', null);
                                $set('region', null);
                            }),
                        Select::make('parent_id')
                            ->label(__('custom.Parent church'))
                            ->searchable()
                            ->preload()
                            ->options(fn (Get $get, ?Church $record): array => self::parentOptions($get('level'), $record))
                            ->required(fn (Get $get): bool => self::level($get('level')) === ChurchLevel::Congregation)
                            ->visible(fn (Get $get): bool => filled($get('level')) && self::level($get('level')) !== ChurchLevel::National)
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if ($state === null) {
                                    return;
                                }

                                $parent = Church::query()->find($state);

                                if ($parent === null) {
                                    return;
                                }

                                if ($parent->country_id !== null) {
                                    $set('country_id', $parent->country_id);
                                }

                                if ($parent->state_id !== null) {
                                    $set('state_id', $parent->state_id);
                                    $set('brazil_state_id', $parent->state_id);
                                    $set('foreign_state', $parent->state?->name);
                                }

                                if ($parent->city_id !== null) {
                                    $set('city_id', $parent->city_id);
                                    $set('brazil_city_id', $parent->city_id);
                                    $set('foreign_city', $parent->city?->name);
                                }

                                if ($parent->region !== null && ChurchHierarchy::isBrazil($parent->country_id)) {
                                    $set('region', $parent->region->value);
                                } else {
                                    $set('region', null);
                                }
                            }),
                        Select::make('region')
                            ->label(__('custom.Region'))
                            ->options(BrazilRegion::class)
                            ->required(fn (Get $get): bool => self::level($get('level'))?->requiresBrazilRegion(
                                ChurchHierarchy::isBrazil($get('country_id'))
                            ) ?? false)
                            ->visible(fn (Get $get): bool => self::level($get('level')) === ChurchLevel::Regional
                                && ChurchHierarchy::isBrazil($get('country_id')))
                            ->helperText(__('custom.Brazilian geographic region for regional headquarters.')),
                        TextInput::make('denomination')
                            ->label(__('custom.Denomination'))
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make(__('custom.Contact'))
                    ->schema([
                        TextInput::make('phone')
                            ->label(__('custom.Phone'))
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('custom.Email'))
                            ->email()
                            ->maxLength(255),
                        TextInput::make('website')
                            ->label(__('custom.Website'))
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->footer(function (mixed $livewire): array {
                        if (! is_object($livewire) || ! method_exists($livewire, 'getFormActionsContentComponent')) {
                            return [];
                        }

                        return [
                            $livewire->getFormActionsContentComponent()
                                ->sticky(false),
                        ];
                    }),
                Section::make(__('custom.Location'))
                    ->schema([
                        Select::make('country_id')
                            ->label(__('custom.Country'))
                            ->options(fn (): array => Country::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->default(fn (): ?int => ChurchHierarchy::brazilCountryId())
                            ->required(fn (Get $get): bool => self::level($get('level'))?->requiresCountry() ?? false)
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('postal_code', null);
                                $set('state_id', null);
                                $set('city_id', null);
                                $set('brazil_state_id', null);
                                $set('brazil_city_id', null);
                                $set('neighborhood_id', null);
                                $set('foreign_state', null);
                                $set('foreign_city', null);
                                $set('address', null);
                                $set('latitude', null);
                                $set('longitude', null);
                                $set('timezone', 'America/Sao_Paulo');
                                $set('region', null);
                            }),
                        TextInput::make('postal_code')
                            ->label(__('custom.Zip Code'))
                            ->maxLength(10)
                            ->helperText(fn (Get $get): ?string => ChurchHierarchy::isBrazil($get('country_id'))
                                ? __('custom.Fill in the ZIP code to load the address automatically')
                                : null)
                            ->visible(fn (Get $get): bool => ChurchHierarchy::isBrazil($get('country_id')) || blank($get('country_id')))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                if (! ChurchHierarchy::isBrazil($get('country_id'))) {
                                    return;
                                }

                                $resolved = app(ResolveAddressByZipCode::class)->handle($state);

                                if ($resolved === null) {
                                    return;
                                }

                                $set('postal_code', $resolved['zip_code']);
                                $set('country_id', $resolved['country_id']);
                                $set('state_id', $resolved['state_id']);
                                $set('city_id', $resolved['city_id']);
                                $set('brazil_state_id', $resolved['state_id']);
                                $set('brazil_city_id', $resolved['city_id']);
                                $set('neighborhood_id', $resolved['neighborhood_id']);
                                $set('address', $resolved['address']);
                                $set('latitude', $resolved['latitude']);
                                $set('longitude', $resolved['longitude']);
                                self::syncTimezoneFromCity($set, $resolved['city_id']);
                            }),
                        Hidden::make('state_id'),
                        Hidden::make('city_id'),
                        Hidden::make('latitude'),
                        Hidden::make('longitude'),
                        Select::make('brazil_state_id')
                            ->label(__('custom.State'))
                            ->options(function (Get $get): array {
                                $query = State::query()->orderBy('name');

                                if ($get('country_id')) {
                                    $query->where('country_id', $get('country_id'));
                                }

                                if (
                                    self::level($get('level')) === ChurchLevel::Regional
                                    && ChurchHierarchy::isBrazil($get('country_id'))
                                    && $get('region')
                                ) {
                                    $query->where('region', $get('region'));
                                }

                                $parent = $get('parent_id') ? Church::query()->find($get('parent_id')) : null;

                                if (
                                    $parent?->level === ChurchLevel::Regional
                                    && $parent->region !== null
                                    && ChurchHierarchy::isBrazil($get('country_id'))
                                ) {
                                    $query->where('region', $parent->region);
                                }

                                if ($parent?->level === ChurchLevel::State && $parent->state_id !== null) {
                                    $query->whereKey($parent->state_id);
                                }

                                return $query->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => (self::level($get('level'))?->requiresState() ?? false)
                                && ChurchHierarchy::isBrazil($get('country_id')))
                            ->visible(fn (Get $get): bool => ChurchHierarchy::isBrazil($get('country_id'))
                                && (
                                    in_array(
                                        self::level($get('level')),
                                        [ChurchLevel::State, ChurchLevel::Municipal, ChurchLevel::Congregation, ChurchLevel::National],
                                        true,
                                    ) || filled($get('state_id'))
                                ))
                            ->afterStateHydrated(function (Get $get, Set $set): void {
                                if (ChurchHierarchy::isBrazil($get('country_id'))) {
                                    $set('brazil_state_id', $get('state_id'));
                                }
                            })
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $set('state_id', $state);
                                $set('city_id', null);
                                $set('brazil_city_id', null);
                                $set('neighborhood_id', null);
                            }),
                        Select::make('brazil_city_id')
                            ->label(__('custom.City'))
                            ->options(function (Get $get): array {
                                $query = City::query()->orderBy('name');

                                if ($get('state_id')) {
                                    $query->where('state_id', $get('state_id'));
                                }

                                $parent = $get('parent_id') ? Church::query()->find($get('parent_id')) : null;

                                if ($parent?->level === ChurchLevel::Municipal && $parent->city_id !== null) {
                                    $query->whereKey($parent->city_id);
                                }

                                return $query->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => (self::level($get('level'))?->requiresCity() ?? false)
                                && ChurchHierarchy::isBrazil($get('country_id')))
                            ->visible(fn (Get $get): bool => ChurchHierarchy::isBrazil($get('country_id'))
                                && (self::level($get('level'))?->requiresCity() ?? false))
                            ->afterStateHydrated(function (Get $get, Set $set): void {
                                if (ChurchHierarchy::isBrazil($get('country_id'))) {
                                    $set('brazil_city_id', $get('city_id'));
                                }
                            })
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $set('city_id', $state);
                                $set('neighborhood_id', null);
                                self::syncTimezoneFromCity($set, $state);
                            }),
                        Select::make('neighborhood_id')
                            ->label(__('custom.Neighborhood'))
                            ->options(fn (Get $get): array => $get('city_id')
                                ? Neighborhood::query()
                                    ->where('city_id', $get('city_id'))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray()
                                : [])
                            ->searchable()
                            ->visible(fn (Get $get): bool => ChurchHierarchy::isBrazil($get('country_id'))
                                && (self::level($get('level'))?->requiresCity() ?? false)),
                        TextInput::make('foreign_state')
                            ->label(__('custom.State/Province'))
                            ->dehydrated(false)
                            ->visible(fn (Get $get): bool => ChurchHierarchy::isForeign($get('country_id'))
                                && (self::level($get('level'))?->requiresState() ?? false))
                            ->required(fn (Get $get): bool => ChurchHierarchy::isForeign($get('country_id'))
                                && (self::level($get('level'))?->requiresState() ?? false)
                                && blank($get('state_id')))
                            ->afterStateHydrated(function (Get $get, Set $set): void {
                                if ($get('state_id') && ChurchHierarchy::isForeign($get('country_id'))) {
                                    $set('foreign_state', State::query()->find($get('state_id'))?->name);
                                }
                            })
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                $set('state_id', app(ResolveForeignLocation::class)
                                    ->resolveState((int) $get('country_id'), $state));
                                $set('city_id', null);
                                $set('foreign_city', null);
                            }),
                        TextInput::make('foreign_city')
                            ->label(__('custom.City'))
                            ->dehydrated(false)
                            ->visible(fn (Get $get): bool => ChurchHierarchy::isForeign($get('country_id'))
                                && (self::level($get('level'))?->requiresCity() ?? false))
                            ->required(fn (Get $get): bool => ChurchHierarchy::isForeign($get('country_id'))
                                && (self::level($get('level'))?->requiresCity() ?? false)
                                && blank($get('city_id')))
                            ->afterStateHydrated(function (Get $get, Set $set): void {
                                if ($get('city_id') && ChurchHierarchy::isForeign($get('country_id'))) {
                                    $set('foreign_city', City::query()->find($get('city_id'))?->name);
                                }
                            })
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                $cityId = app(ResolveForeignLocation::class)->resolveCity(
                                    (int) $get('country_id'),
                                    $get('state_id') ? (int) $get('state_id') : null,
                                    $state,
                                );
                                $set('city_id', $cityId);
                                self::syncTimezoneFromCity($set, $cityId);
                            }),
                        TextInput::make('address')
                            ->label(__('custom.Address'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('area_code')
                            ->label(__('custom.Area Code'))
                            ->maxLength(5)
                            ->visible(fn (Get $get): bool => ChurchHierarchy::isBrazil($get('country_id')) || blank($get('country_id'))),
                        TextInput::make('timezone')
                            ->label(__('custom.Timezone'))
                            ->default('America/Sao_Paulo')
                            ->disabled()
                            ->saved()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    protected static function level(mixed $value): ?ChurchLevel
    {
        if ($value instanceof ChurchLevel) {
            return $value;
        }

        if (is_string($value) || is_int($value)) {
            return ChurchLevel::tryFrom((string) $value);
        }

        return null;
    }

    protected static function syncTimezoneFromCity(Set $set, int|string|null $cityId): void
    {
        if ($cityId === null || $cityId === '') {
            $set('timezone', 'America/Sao_Paulo');

            return;
        }

        $timezone = City::query()->find($cityId)?->timezone;

        $set('timezone', filled($timezone) ? $timezone : 'America/Sao_Paulo');
    }

    /**
     * @return array<int|string, string>
     */
    protected static function parentOptions(mixed $levelValue, ?Church $record): array
    {
        $level = self::level($levelValue);

        if ($level === null) {
            return [];
        }

        $allowed = $level->allowedParentLevels();

        if ($allowed === []) {
            return [];
        }

        return Church::query()
            ->whereIn('level', $allowed)
            ->when($record !== null, fn (Builder $query): Builder => $query->whereKeyNot($record->getKey()))
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Church $church): array => [
                $church->id => $church->name.' ('.$church->level->getLabel().')',
            ])
            ->all();
    }
}
