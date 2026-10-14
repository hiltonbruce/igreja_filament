<?php

namespace App\Filament\Secretary\Resources\Letters\Pages;

use App\Filament\Secretary\Resources\Letters\LetterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManageLetters extends ManageRecords
{
    protected static string $resource = LetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('custom.Register').' '.__('custom.Letter'))
                ->modalWidth(Width::ThreeExtraLarge),
        ];
    }
}
