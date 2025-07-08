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

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                        Wizard\Step::make('Dados Pessoais')
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('Nome')
                                    ->required(),
                                Forms\Components\TextInput::make('last_name')
                                    ->label('Sobrenome')
                                    ->columnSpan(2)
                                    ->required(),
                                Forms\Components\DatePicker::make('birth_date')
                                    ->label('Data de Nascimento')
                                    ->required(),
                                Forms\Components\Radio::make('brazilian_born')
                                    ->label('Brasileiro')
                                    ->options([
                                        'true' => 'Sim',
                                        'false' => 'Não',
                                    ])
                                    ->inline()
                                    ->inlineLabel(false)
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('country_of_birth_id')
                                    ->label('País de Nascimento')
                                    ->required()
                                    ->visible(fn (Forms\Get $get) => $get('brazilian_born') === 'false'),
                                Forms\Components\TextInput::make('state_of_birth_id')
                                    ->label('Estado de Nascimento')
                                    ->required(),
                                Forms\Components\TextInput::make('city_of_birth_id')
                                    ->label('Cidade de Nascimento')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->required()
                                    ->email(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Telefone')
                                    ->required(),
                            ])
                            ->columns(3),
                        Wizard\Step::make('Eclesiástico')
                            ->schema([
                                // ...
                            ]),
                        Wizard\Step::make('Familiar')
                            ->schema([
                                // ...
                            ]),
                        Wizard\Step::make('Profissional')
                            ->schema([
                                // ...
                            ]),
                        Wizard\Step::make('Financeiro')
                            ->schema([
                                // ...
                            ]),
                        Wizard\Step::make('Observações')
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
