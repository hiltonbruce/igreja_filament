<?php

namespace App\Filament\Secretary\Resources\Churches\Pages;

use App\Filament\Secretary\Resources\Churches\ChurchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChurches extends ListRecords
{
    protected static string $resource = ChurchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
