<?php

namespace App\Filament\Admin\Resources\Members;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
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

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;
    protected static ?string $modelLabel = 'Membros';

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
                                TextInput::make('country_of_birth_id')
                                    ->label(__('custom.Country of Birth'))
                                    ->required()
                                    ->visible(fn (Get $get) => $get('brazilian_born') === 'false'),
                                TextInput::make('state_of_birth_id')
                                    ->label(__('custom.State of Birth'))
                                    ->required(),
                                TextInput::make('city_of_birth_id')
                                    ->label(__('custom.City of Birth'))
                                    ->required(),
                                TextInput::make('email')
                                    ->label(__('custom.Email'))
                                    ->required()
                                    ->email(),
                                TextInput::make('phone')
                                    ->label(__('custom.Phone'))
                                    ->required(),
                            ])
                            ->columns(3),
                        Step::make(__('custom.Ecclesiastical'))
                            ->schema([
                                // ...
                            ]),
                        Step::make(__('custom.Family'))
                            ->schema([
                                // ...
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
