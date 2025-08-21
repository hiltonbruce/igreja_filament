<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MemberResource\Pages;
use App\Filament\Admin\Resources\MemberResource\RelationManagers;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;
    protected static ?string $modelLabel = 'Membros';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                        Wizard\Step::make(__('custom.Personal Data'))
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('custom.Name'))
                                    ->columnSpanFull()
                                    ->required(),
                                Forms\Components\TextInput::make('father_name')
                                    ->label(__('custom.Father Name'))
                                    ->columnSpanFull()
                                    ->required(),
                                Forms\Components\TextInput::make('mother_name')
                                    ->label(__('custom.Mother Name'))
                                    ->columnSpanFull()
                                    ->required(),
                                Forms\Components\DatePicker::make('birth_date')
                                    ->label(__('custom.Birth Date'))
                                    ->required(),
                                Forms\Components\Radio::make('sex')
                                    ->label(__('custom.Sex'))
                                    ->inline()
                                    ->inlineLabel(false)
                                    ->options([
                                        'male' => __('custom.Male'),
                                        'female' => __('custom.Female'),
                                    ])
                                    ->required(),
                                Forms\Components\Radio::make('brazilian_born')
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
                                Forms\Components\TextInput::make('country_of_birth_id')
                                    ->label(__('custom.Country of Birth'))
                                    ->required()
                                    ->visible(fn (Forms\Get $get) => $get('brazilian_born') === 'false'),
                                Forms\Components\TextInput::make('state_of_birth_id')
                                    ->label(__('custom.State of Birth'))
                                    ->required(),
                                Forms\Components\TextInput::make('city_of_birth_id')
                                    ->label(__('custom.City of Birth'))
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->label(__('custom.Email'))
                                    ->required()
                                    ->email(),
                                Forms\Components\TextInput::make('phone')
                                    ->label(__('custom.Phone'))
                                    ->required(),
                            ])
                            ->columns(3),
                        Wizard\Step::make(__('custom.Ecclesiastical'))
                            ->schema([
                                // ...
                            ]),
                        Wizard\Step::make(__('custom.Family'))
                            ->schema([
                                // ...
                            ]),
                        Wizard\Step::make(__('custom.Professional'))
                            ->schema([
                                // ...
                            ]),
                        Wizard\Step::make(__('custom.Financial'))
                            ->schema([
                                // ...
                            ]),
                        Wizard\Step::make(__('custom.Observations'))
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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::Full),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMembers::route('/'),
            // 'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
