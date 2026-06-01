<?php

namespace App\Filament\Admin\Resources\Streets\Pages;

use App\Filament\Admin\Resources\Streets\StreetResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStreet extends ViewRecord
{
    protected static string $resource = StreetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
