<?php

namespace App\Filament\Admin\Resources\Members;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Admin\Resources\Members\Pages\ListMembers;
use App\Filament\Admin\Resources\Members\Pages\EditMember;
use App\Filament\Admin\Resources\MemberResource\Pages;
use App\Filament\Admin\Resources\MemberResource\RelationManagers;
use App\Models\Member;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

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
                                ->directory('members_photos')
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                    $recordId = (string) (request()->route('record') ?? 'new');
                                    $timestamp = now()->format('YmdHi');
                                    $originalBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                                    $ext = $file->getClientOriginalExtension();
                                    $safeBase = Str::slug($originalBase, '_');
                                    return $recordId . '_' . $timestamp . '_' . $safeBase . '.' . $ext;
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
                                ->options(\App\Models\Country::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->required()
                                ->visible(fn(Get $get) => $get('brazilian_born') === 'false')
                                ->live()
                                ->afterStateUpdated(fn (callable $set) => $set('state_of_birth_id', null)),
                            Select::make('state_of_birth_id')
                                ->label(__('custom.State of Birth'))
                                ->options(fn (Get $get): array =>
                                    $get('country_of_birth_id')
                                        ? \App\Models\State::query()
                                            ->where('country_id', $get('country_of_birth_id'))
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray()
                                        : []
                                )
                                ->searchable()
                                ->required()
                                ->visible(fn(Get $get) => $get('brazilian_born') === 'false' && $get('country_of_birth_id'))
                                ->live()
                                ->afterStateUpdated(fn (callable $set) => $set('city_of_birth_id', null)),
                            Select::make('city_of_birth_id')
                                ->label(__('custom.City of Birth'))
                                ->options(fn (Get $get): array =>
                                    $get('state_of_birth_id')
                                        ? \App\Models\City::query()
                                            ->where('state_id', $get('state_of_birth_id'))
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray()
                                        : []
                                )
                                ->searchable()
                                ->required()
                                ->visible(fn(Get $get) => $get('brazilian_born') === 'false' && $get('state_of_birth_id')),
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
                            // Endereço residencial
                            Select::make('country_id')
                                ->label(__('custom.Country'))
                                ->options(\App\Models\Country::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->default(55),
                            Select::make('state_id')
                                ->label(__('custom.State'))
                                ->options(\App\Models\State::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable(),
                            Select::make('city_id')
                                ->label(__('custom.City'))
                                ->options(\App\Models\City::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable(),
                            Select::make('neighborhood_id')
                                ->label(__('custom.Neighborhood'))
                                ->options(\App\Models\Neighborhood::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable(),
                            TextInput::make('address')
                                ->label(__('custom.Address'))
                                ->columnSpanFull(),
                            TextInput::make('number')
                                ->label(__('custom.Number')),
                            TextInput::make('complement')
                                ->label(__('custom.Complement')),
                            TextInput::make('zip_code')
                                ->label(__('custom.Zip Code')),
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
                                ->visible(fn(Get $get) => (bool) $get('baptized_in_spirit') === true),
                            DatePicker::make('baptized')
                                ->label(__('custom.Baptized Date')),
                        ]),
                    Step::make(__('custom.Family'))
                        ->schema([
                            Toggle::make('matrimonial_status')
                                ->label(__('custom.Matrimonial Status')),
                            Select::make('mother_member_id')
                                ->label(__('custom.Mother Member'))
                                ->options(\App\Models\Member::query()->orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable(),
                            Select::make('father_member_id')
                                ->label(__('custom.Father Member'))
                                ->options(\App\Models\Member::query()->orderBy('name')->pluck('name', 'id')->toArray())
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
                        ])

                ])->columnSpanFull(),
            ]);
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
            //
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
