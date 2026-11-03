<?php

namespace App\Filament\Secretary\Resources\UnitTypes\Pages;

use App\Filament\Secretary\Resources\UnitTypes\UnitTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUnitTypes extends ListRecords
{
    protected static string $resource = UnitTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
