<?php

namespace App\Filament\Secretary\Resources\Members;

use App\Actions\ResolveAddressByZipCode;
use App\Actions\ResolveForeignLocation;
use App\Filament\Secretary\Resources\Members\Pages\EditMember;
use App\Filament\Secretary\Resources\Members\Pages\ListMembers;
use App\Filament\Secretary\Resources\Members\RelationManagers\MemberPhotosRelationManager;
use App\Models\City;
use App\Models\Country;
use App\Models\Member;
use App\Models\Neighborhood;
use App\Models\State;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $modelLabel = null;

    public static function getModelLabel(): string
    {
        return __('custom.Member');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.Members');
    }

    public static function getNavigationLabel(): string
    {
        return __('custom.Members');
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make(__('custom.Personal Data'))
                        ->schema([
                            TextInput::make('name')
                                ->label(__('custom.Name'))
                                ->columnSpanFull()
                                ->required(),
                            TextInput::make('father_name')
                                ->label(__('custom.Father Name'))
                                ->columnSpanFull()
                                ->required(),
                            TextInput::make('mother_name')
                                ->label(__('custom.Mother Name'))
                                ->columnSpanFull()
                                ->required(),
                            DatePicker::make('birth_date')
                                ->label(__('custom.Birth Date'))
                                ->required(),
                            Radio::make('sex')
                                ->label(__('custom.Sex'))
                                ->inline()
                                ->inlineLabel(false)
                                ->options([
                                    'male' => __('custom.Male'),
                                    'female' => __('custom.Female'),
                                ])
                                ->required(),
                            Toggle::make('donor')
                                ->label(__('custom.Organ Donor')),
                            Select::make('blood_type')
                                ->label(__('custom.Blood Type'))
                                ->options([
                                    'O+' => 'O+', 'O-' => 'O-', 'A+' => 'A+', 'A-' => 'A-',
                                    'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-',
                                ]),
                            FileUpload::make('photo')
                                ->label(__('custom.Photo'))
                                ->image()
                                ->disk('local')
                                ->directory('members/photos')
                                ->visibility('private')
                                ->preventFilePathTampering()
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                    $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');

                                    return (string) Str::ulid().'.'.$extension;
                                })
                                ->columnSpanFull(),
                            Radio::make('brazilian_born')
                                ->label(__('custom.Brazilian'))
                                ->options([
                                    'true' => __('custom.Yes'),
                                    'false' => __('custom.No'),
                                ])
                                ->default('true')
                                ->inline()
                                ->inlineLabel(false)
                                ->required()
                                ->live(),
                            Select::make('country_of_birth_id')
                                ->label(__('custom.Country of Birth'))
                                ->options(Country::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->required()
                                ->visible(fn (Get $get) => $get('brazilian_born') === 'false')
                                ->live()
                                ->afterStateUpdated(fn (callable $set) => $set('state_of_birth_id', null)),
                            Select::make('state_of_birth_id')
                                ->label(__('custom.State of Birth'))
                                ->options(fn (Get $get): array => $get('country_of_birth_id')
                                        ? State::query()
                                            ->where('country_id', $get('country_of_birth_id'))
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray()
                                        : []
                                )
                                ->searchable()
                                ->required()
                                ->visible(fn (Get $get) => $get('brazilian_born') === 'false' && $get('country_of_birth_id'))
                                ->live()
                                ->afterStateUpdated(fn (callable $set) => $set('city_of_birth_id', null)),
                            Select::make('city_of_birth_id')
                                ->label(__('custom.City of Birth'))
                                ->options(fn (Get $get): array => $get('state_of_birth_id')
                                        ? City::query()
                                            ->where('state_id', $get('state_of_birth_id'))
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray()
                                        : []
                                )
                                ->searchable()
                                ->required()
                                ->visible(fn (Get $get) => $get('brazilian_born') === 'false' && $get('state_of_birth_id')),
                            Select::make('document_type')
                                ->label(__('custom.Document Type'))
                                ->options([
                                    'CPF' => 'CPF',
                                    'RG' => 'RG',
                                    'PASSAPORTE' => __('custom.Passport'),
                                ])
                                ->default('CPF'),
                            TextInput::make('document')
                                ->label(__('custom.Document'))
                                ->required(),
                            TextInput::make('email')
                                ->label(__('custom.Email'))
                                ->required()
                                ->email(),
                            TextInput::make('phone')
                                ->label(__('custom.Phone'))
                                ->required(),
                            // Endereço residencial — o país define o comportamento:
                            // Brasil usa o CEP (busca local + BrasilAPI) e o exterior
                            // usa campos de texto livre que viram registros no banco.
                            Select::make('country_id')
                                ->label(__('custom.Country'))
                                ->options(fn (): array => Country::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->default(fn (): int => self::brazilCountryId())
                                ->live()
                                ->afterStateUpdated(function (Set $set): void {
                                    $set('zip_code', null);
                                    $set('state_id', null);
                                    $set('city_id', null);
                                    $set('neighborhood_id', null);
                                    $set('foreign_state', null);
                                    $set('foreign_city', null);
                                    $set('address', null);
                                }),
                            TextInput::make('zip_code')
                                ->label(__('custom.Zip Code'))
                                ->helperText(fn (Get $get): ?string => self::isBrazil($get('country_id'))
                                    ? __('custom.Fill in the ZIP code to load the address automatically')
                                    : null)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                    if (! self::isBrazil($get('country_id'))) {
                                        return;
                                    }

                                    $resolved = app(ResolveAddressByZipCode::class)->handle($state);

                                    if ($resolved === null) {
                                        return;
                                    }

                                    $set('zip_code', $resolved['zip_code']);
                                    $set('country_id', $resolved['country_id']);
                                    $set('state_id', $resolved['state_id']);
                                    $set('city_id', $resolved['city_id']);
                                    $set('neighborhood_id', $resolved['neighborhood_id']);
                                    $set('address', $resolved['address']);
                                }),
                            // Brasil: seleção normalizada preenchida pelo CEP.
                            Select::make('state_id')
                                ->label(__('custom.State'))
                                ->options(fn (): array => State::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->visible(fn (Get $get): bool => self::isBrazil($get('country_id'))),
                            Select::make('city_id')
                                ->label(__('custom.City'))
                                ->options(fn (): array => City::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->visible(fn (Get $get): bool => self::isBrazil($get('country_id'))),
                            Select::make('neighborhood_id')
                                ->label(__('custom.Neighborhood'))
                                ->options(fn (): array => Neighborhood::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->visible(fn (Get $get): bool => self::isBrazil($get('country_id'))),
                            // Exterior: texto livre normalizado em registros do banco.
                            TextInput::make('foreign_state')
                                ->label(__('custom.State/Province'))
                                ->dehydrated(false)
                                ->visible(fn (Get $get): bool => self::isForeign($get('country_id')))
                                ->required(fn (Get $get): bool => self::isForeign($get('country_id')))
                                ->afterStateHydrated(function (Get $get, Set $set): void {
                                    if ($get('state_id')) {
                                        $set('foreign_state', State::query()->find($get('state_id'))?->name);
                                    }
                                })
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                    $set('state_id', app(ResolveForeignLocation::class)
                                        ->resolveState((int) $get('country_id'), $state));
                                }),
                            TextInput::make('foreign_city')
                                ->label(__('custom.City'))
                                ->dehydrated(false)
                                ->visible(fn (Get $get): bool => self::isForeign($get('country_id')))
                                ->afterStateHydrated(function (Get $get, Set $set): void {
                                    if ($get('city_id')) {
                                        $set('foreign_city', City::query()->find($get('city_id'))?->name);
                                    }
                                })
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                    $set('city_id', app(ResolveForeignLocation::class)->resolveCity(
                                        (int) $get('country_id'),
                                        $get('state_id') ? (int) $get('state_id') : null,
                                        $state,
                                    ));
                                }),
                            TextInput::make('address')
                                ->label(__('custom.Address'))
                                ->columnSpanFull(),
                            TextInput::make('number')
                                ->label(__('custom.Number')),
                            TextInput::make('complement')
                                ->label(__('custom.Complement')),
                        ])
                        ->columns(3),
                    Step::make(__('custom.Ecclesiastical'))
                        ->schema([
                            Toggle::make('spiritual_situation')
                                ->label(__('custom.Spiritual Situation')),
                            Toggle::make('baptized_in_spirit')
                                ->label(__('custom.Baptized in Spirit'))
                                ->live(),
                            DatePicker::make('baptized_in_spirit_date')
                                ->label(__('custom.Baptized in Spirit Date'))
                                ->visible(fn (Get $get) => (bool) $get('baptized_in_spirit') === true),
                            DatePicker::make('baptized')
                                ->label(__('custom.Baptized Date')),
                        ]),
                    Step::make(__('custom.Family'))
                        ->schema([
                            Toggle::make('matrimonial_status')
                                ->label(__('custom.Matrimonial Status')),
                            Select::make('mother_member_id')
                                ->label(__('custom.Mother Member'))
                                ->options(Member::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable(),
                            Select::make('father_member_id')
                                ->label(__('custom.Father Member'))
                                ->options(Member::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable(),
                        ]),
                    Step::make(__('custom.Professional'))
                        ->schema([
                            // ...
                        ]),
                    Step::make(__('custom.Financial'))
                        ->schema([
                            // ...
                        ]),
                    Step::make(__('custom.Observations'))
                        ->schema([
                            // ...
                        ]),

                ])->columnSpanFull(),
            ]);
    }

    protected static function brazilCountryId(): int
    {
        return once(fn (): int => (int) (Country::query()->where('iso_code', 'BR')->value('id') ?? 0));
    }

    protected static function isBrazil(mixed $countryId): bool
    {
        return filled($countryId) && (int) $countryId === self::brazilCountryId();
    }

    protected static function isForeign(mixed $countryId): bool
    {
        return filled($countryId) && (int) $countryId !== self::brazilCountryId();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Full),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MemberPhotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            // 'create' => Pages\CreateMember::route('/create'),
            'edit' => EditMember::route('/{record}/edit'),
        ];
    }
}
