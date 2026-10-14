<?php

namespace App\Filament\Secretary\Resources\Letters;

use App\Enums\LetterType;
use App\Filament\Secretary\Resources\Letters\Pages\ManageLetters;
use App\Models\Letter;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LetterResource extends Resource
{
    protected static ?string $model = Letter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'destination';

    public static function getModelLabel(): string
    {
        return __('custom.Letter');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.Letters');
    }

    public static function getNavigationLabel(): string
    {
        return __('custom.Letters');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('member_id')
                    ->label(__('custom.Member'))
                    ->relationship('member', 'name')
                    ->searchable()
                    ->required(),
                Select::make('type')
                    ->label(__('custom.Letter type'))
                    ->options(LetterType::class)
                    ->required()
                    ->live()
                    ->helperText(fn (Get $get): ?string => LetterType::fromMixed($get('type')) === LetterType::Change
                        ? __('custom.A change letter sets the member spiritual situation to transferred.')
                        : null),
                DatePicker::make('issued_at')
                    ->label(__('custom.Letter date'))
                    ->default(now()->toDateString())
                    ->required(),
                Select::make('directed_by_member_id')
                    ->label(__('custom.Service directed by'))
                    ->relationship('directedBy', 'name')
                    ->searchable()
                    ->required(),
                TextInput::make('destination')
                    ->label(__('custom.Destination church'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['member', 'directedBy']))
            ->defaultSort('issued_at', 'desc')
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('custom.Member'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('custom.Letter type'))
                    ->badge()
                    ->formatStateUsing(fn (LetterType $state): string => $state->getLabel())
                    ->color(fn (LetterType $state): string => $state->getColor()),
                TextColumn::make('issued_at')
                    ->label(__('custom.Letter date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('destination')
                    ->label(__('custom.Destination church'))
                    ->searchable(),
                TextColumn::make('directedBy.name')
                    ->label(__('custom.Service directed by'))
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('custom.Letter type'))
                    ->options(LetterType::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLetters::route('/'),
        ];
    }
}
