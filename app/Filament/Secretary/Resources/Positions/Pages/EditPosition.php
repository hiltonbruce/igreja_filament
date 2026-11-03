<?php

namespace App\Filament\Secretary\Resources\Positions\Pages;

use App\Filament\Secretary\Resources\Positions\PositionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosition extends EditRecord
{
    protected static string $resource = PositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
