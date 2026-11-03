<?php

namespace App\Filament\Secretary\Resources\UnitTypes\Pages;

use App\Filament\Secretary\Resources\UnitTypes\UnitTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUnitType extends EditRecord
{
    protected static string $resource = UnitTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
