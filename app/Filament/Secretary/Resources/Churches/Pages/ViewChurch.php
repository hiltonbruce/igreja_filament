<?php

namespace App\Filament\Secretary\Resources\Churches\Pages;

use App\Filament\Secretary\Resources\Churches\ChurchResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewChurch extends ViewRecord
{
    protected static string $resource = ChurchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
