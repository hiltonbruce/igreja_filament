<?php

namespace App\Filament\Admin\Resources\Neighborhoods\Pages;

use App\Filament\Admin\Resources\Neighborhoods\NeighborhoodResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNeighborhood extends ViewRecord
{
    protected static string $resource = NeighborhoodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
